<?php

namespace App\Http\Controllers\Auth;

use App\User;
use App\Models\Role;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class OAuth2Controller extends Controller
{
	/**
	 * Create a new controller instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
//		$this->middleware('auth:api')->except(['handleProviderCallback']);
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
//			    'state' => $request->input('state'),
			    'grant_type' => 'authorization_code'
			]
		]);
		$data = curl_exec($handle);

//		$handle2 = curl_init();
//		curl_setopt_array($handle2, [
//			CURLOPT_RETURNTRANSFER => 1,
//			CURLOPT_URL => 'https://api.github.com/user',
//			CURLOPT_HTTPHEADER => [
//				'Content-Type: application/json',
//				'Authorization: Bearer ' . $data['access_token']
//			]
//		]);
//		$result = curl_exec($handle2);
//		curl_close($handle2);

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