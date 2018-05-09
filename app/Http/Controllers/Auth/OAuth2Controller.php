<?php

namespace App\Http\Controllers\Auth;

use App\User;
use App\Models\Role;
use App\Http\Controllers\Controller;

class OAuth2Controller extends Controller
{
	protected function githubAuth($req)
	{
		$handle = curl_init();
		curl_setopt($handle, CURLOPT_URL, 'https://github.com/login/oauth/access_token');
		curl_setopt($handle, CURLOPT_POST, 1);

		curl_setopt_array($handle, [
			CURLOPT_RETURNTRANSFER => 1,
			CURLOPT_URL => 'https://github.com/login/oauth/access_token',
			CURLOPT_POST => 1,
			CURLOPT_POSTFIELDS => [
				client_id => config('services.github.client_id'),
			    client_secret => config('services.github.client_secret'),
			    code => $req->body->code,
			    redirect_uri => $req->body->redirectUri,
			    state => $req->body->state,
			    grant_type => 'authorization_code'
			]
		]);

		$data = curl_exec($handle);
		curl_close($handle);

		return $this->returnSuccessMessage('data', json_encode($data));
	}
}