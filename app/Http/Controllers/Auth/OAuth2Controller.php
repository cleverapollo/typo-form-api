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
		}
	}

	/**
	 *
	 *
	 * @param  $request
	 * @return \Illuminate\Http\JsonResponse
	 */
	protected function githubAuth($request)
	{
		$handle = curl_init();
		curl_setopt($handle, CURLOPT_URL, 'https://github.com/login/oauth/access_token');
		curl_setopt($handle, CURLOPT_POST, 1);

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
		curl_close($handle);

		return $this->returnSuccessMessage('data', json_decode($data));
	}
}