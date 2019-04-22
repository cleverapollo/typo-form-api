<?php

namespace App\Http\Controllers;

use App\Http\Resources\AclResource;
use Auth;

class AclController extends Controller
{
	/**
	 * Create a new controller instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		$this->middleware('auth:api');
	}

	public function index()
	{
		$user = Auth::user();
		return $this->returnSuccessMessage('acl', AclResource::collection($user->getAbilities()));
	}
}
