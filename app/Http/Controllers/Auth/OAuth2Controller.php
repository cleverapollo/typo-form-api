<?php

namespace App\Http\Controllers\Auth;

use App\User;
use App\Models\Role;
use Carbon\Carbon;
use App\Http\Resources\AuthResource;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class OAuth2Controller extends Controller
{
	/**
	 * Login with social
	 *
	 * @param Request $request
	 *
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function signin(Request $request)
	{
		$social_id = $request->input('id', null);
		$provider = $request->input('provider', null);

		if (!$social_id || ($provider != 'github' && $provider != 'facebook' && $provider != 'google' && $provider != 'live')) {
			return $this->returnErrorMessage(503, 'Invalid request');
		}

        $user = User::where([
            ['social_id', '=', $social_id],
            ['provider', '=', $provider],
        ])->first();

        if (empty($user)) {
            $registered = User::where('email', $request->input('email', ''))->first();
            if (!empty($registered)) {
                return $this->returnErrorMessage(503, 'Sorry. There was an error while creating account. Please try again later.');
            }

            $user = $this->createUser($request);
        }

		// Login user
		$api_token = base64_encode(str_random(40));
		while (!is_null(User::where('api_token', $api_token)->first())) {
			$api_token = base64_encode(str_random(40));
		}
		$expire_date = Carbon::now();
		$user->update(['api_token' => $api_token, 'expire_date' => $expire_date]);

		return response()->json([
			'status' => 'success',
			'user' => new AuthResource($user)
		], 200);
	}

	/**
	 * Find or create user based on social_id and provider
	 *
	 * @param  Request $request
	 *
	 * @return \Illuminate\Contracts\Auth\Authenticatable|mixed $user
	 */
	public function createUser(Request $request)
	{
		return User::create([
			'first_name' => $request->input('first_name', ''),
			'last_name' => $request->input('last_name', ''),
			'email' => $request->input('email', ''),
			'password' => '',
			'social_id' => $request->input('id', null),
			'provider' => $request->input('provider', null),
			'role_id' => Role::where('name', 'User')->first()->id
		]);
	}

	/**
	 * Handle Social OAuth2 provider callback
	 *
	 * @param  $provider
	 * @param  Request $request
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function handleProviderCallback($provider, Request $request)
	{
		switch ($provider)
		{
			case 'github':
				return $this->githubAuth($request);

			case 'facebook':
				return $this->facebookAuth($request);

			case 'google':
				return $this->googleAuth($request);

			case 'live':
				return $this->liveAuth($request);
		}

		return $this->returnErrorMessage(503, 'Invalid request');
	}

	/**
	 * Get Github access token
	 *
	 * @param  Request $request
	 * @return \Illuminate\Http\JsonResponse
	 */
	protected function githubAuth(Request $request)
	{
		$handle = curl_init();
		curl_setopt_array($handle, [
			CURLOPT_RETURNTRANSFER => 1,
			CURLOPT_URL => 'https://github.com/login/oauth/access_token',
			CURLOPT_HTTPHEADER => [
				'Accept:application/json'
			],
			CURLOPT_POST => 1,
			CURLOPT_POSTFIELDS => [
				'client_id' => config('services.github.client_id'),
			    'client_secret' => config('services.github.client_secret'),
			    'code' => $request->input('code'),
			    'redirect_uri' => $request->input('redirectUri'),
			    'grant_type' => 'authorization_code'
			]
		]);
		$data = curl_exec($handle);

		if (curl_error($handle)) {
			return response()->json(curl_error($handle), 500);
		}

		return response()->json(json_decode($data), 200);
	}

	/**
	 * Get Facebook access token
	 *
	 * @param  Request $request
	 * @return \Illuminate\Http\JsonResponse
	 */
	protected function facebookAuth(Request $request)
	{
		$handle = curl_init();
		curl_setopt_array($handle, [
			CURLOPT_RETURNTRANSFER => 1,
			CURLOPT_URL => 'https://graph.facebook.com/v2.4/oauth/access_token',
			CURLOPT_HTTPHEADER => [
				'Content-Type: application/json'
			],
			CURLOPT_POST => 1,
			CURLOPT_POSTFIELDS => [
				'client_id' => config('services.facebook.client_id'),
				'client_secret' => config('services.facebook.client_secret'),
				'code' => $request->input('code'),
				'redirect_uri' => $request->input('redirectUri')
			]
		]);
		$data = curl_exec($handle);
		curl_close($handle);

		return $this->returnSuccessMessage('data', json_decode($data));
	}

	/**
	 * Get Google access token
	 *
	 * @param  Request $request
	 * @return \Illuminate\Http\JsonResponse
	 */
	protected function googleAuth(Request $request)
	{
		$handle = curl_init();
		curl_setopt_array($handle, [
			CURLOPT_RETURNTRANSFER => 1,
			CURLOPT_URL => 'https://accounts.google.com/o/oauth2/token',
			CURLOPT_HTTPHEADER => [
				'Content-Type: application/x-www-form-urlencoded'
			],
			CURLOPT_POST => 1,
			CURLOPT_POSTFIELDS => 'client_id=' . config('services.google.client_id')
								. '&client_secret=' . config('services.google.client_secret')
								. '&code=' . $request->input('code')
								. '&redirect_uri=' . $request->input('redirectUri')
								. '&grant_type=authorization_code'
		]);
		$data = curl_exec($handle);

		if (curl_error($handle)) {
			return response()->json(curl_error($handle), 500);
		}

		return response()->json(json_decode($data), 200);
	}

	/**
	 * Get Live access token
	 *
	 * @param  Request $request
	 * @return \Illuminate\Http\JsonResponse
	 */
	protected function liveAuth(Request $request)
	{
		$handle = curl_init();
		curl_setopt_array($handle, [
			CURLOPT_RETURNTRANSFER => 1,
			CURLOPT_URL => 'https://login.live.com/oauth20_token.srf',
			CURLOPT_HTTPHEADER => [
				'Content-Type: application/x-www-form-urlencoded'
			],
			CURLOPT_POST => 1,
			CURLOPT_POSTFIELDS => 'client_id=' . config('services.live.client_id')
				. '&client_secret=' . config('services.live.client_secret')
				. '&code=' . $request->input('code')
				. '&redirect_uri=' . $request->input('redirectUri')
				. '&grant_type=authorization_code'
		]);
		$data = curl_exec($handle);

		if (curl_error($handle)) {
			return response()->json(curl_error($handle), 500);
		}

		return response()->json(json_decode($data), 200);
	}
}