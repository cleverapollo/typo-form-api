<?php

namespace App\Http\Foundation\Auth;

use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Auth\Events\Registered;

trait RegistersUsers
{
	use RedirectsUsers;

	/**
	 * Show the application registration form.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function showRegistrationForm()
	{
		return view('auth.register');
	}

	/**
	 * Handle a registration request for the application.
	 *
	 * @param  \Illuminate\Http\Request $request
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function register(Request $request)
	{
		$this->validate($request, [
			'first_name' => 'required|string|max:191',
			'last_name' => 'required|string|max:191',
			'email' => 'required|email|max:191|unique:users',
			'password' => 'required|string|min:6|max:191'
		]);

		event(new Registered($user = $this->create($request->only('first_name', 'last_name', 'email', 'password'))));

		if ($user) {
			$user->role_id = Role::where('name', 'User')->id;
			$user->save();

			return response()->json([
				'status' => 'success',
				'message' => 'Congratulations! Your account has been created successfully.'
			], 200);
		}

		return response()->json([
			'status' => 'fail',
			'message' => 'Sorry. There was an error while creating account. Please try again later.'
		], 503);
	}

	/**
	 * Get the guard to be used during registration.
	 *
	 * @return \Illuminate\Contracts\Auth\StatefulGuard
	 */
	protected function guard()
	{
		return Auth::guard();
	}

	/**
	 * The user has been registered.
	 *
	 * @param  \Illuminate\Http\Request $request
	 * @param  mixed $user
	 *
	 * @return mixed
	 */
	protected function registered(Request $request, $user)
	{
		//
	}
}
