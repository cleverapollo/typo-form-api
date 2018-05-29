<?php

namespace App\Http\Controllers;

use Auth;
use Exception;
use App\Http\Resources\UserResource;
use App\Notifications\InformedNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
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

	/**
	 * Display the specified resource.
	 *
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function show()
	{
		$user = Auth::user();

		return $this->returnSuccessMessage('user', new UserResource($user));
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  \Illuminate\Http\Request $request
	 *
	 * @return \Illuminate\Http\JsonResponse
	 * @throws \Illuminate\Validation\ValidationException
	 */
	public function update(Request $request)
	{
		$this->validate($request, [
			'first_name' => 'filled|max:191',
			'last_name' => 'filled|max:191'
		]);

		try {
			$user = Auth::user();
			if ($user->fill($request->only('first_name', 'last_name'))->save()) {
				// Send notification email to user
				if ($user->email) {
					$user->notify(new InformedNotification('Your account has been updated successfully.'));
				}

				return $this->returnSuccessMessage('user', new UserResource($user));
			}

			// Send error if user is not updated
			return $this->returnError('user', 503, 'update');
		} catch (Exception $e) {
			// Send error
			return $this->returnErrorMessage(503, $e->getMessage());
		}
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function destroy()
	{
		try {
			if (Auth::user()->delete()) {
				return $this->returnSuccessMessage('message', 'User has been deleted successfully.');
			}

			// Send error if there is an error on delete
			return $this->returnError('user', 503, 'delete');
		} catch (Exception $e) {
			// Send error
			return $this->returnErrorMessage(503, $e->getMessage());
		}
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  \Illuminate\Http\Request $request
	 *
	 * @return \Illuminate\Http\JsonResponse
	 * @throws \Illuminate\Validation\ValidationException
	 */
	public function updateEmail(Request $request)
	{
		$this->validate($request, [
			'email' => 'required|email|max:191',
			'password' => 'required|min:6|max:191'
		]);

		try {
			$user = Auth::user();
			if (Hash::check($request->input('password'), $user->password)) {
				if ($user->update(['email' => $request->input('email')])) {
					// Send notification email to user
					if ($user->email) {
						$user->notify(new InformedNotification('Email has been updated successfully.'));
					}

					return $this->returnSuccessMessage('user', new UserResource($user));
				}

				// Send error if there is an error on update user email
				return $this->returnError('user email', 503, 'update');
			}

			return $this->returnErrorMessage(403, 'Invalid password.');
		} catch (Exception $e) {
			// Send error
			return $this->returnErrorMessage(503, $e->getMessage());
		}
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  \Illuminate\Http\Request $request
	 *
	 * @return \Illuminate\Http\JsonResponse
	 * @throws \Illuminate\Validation\ValidationException
	 */
	public function updatePassword(Request $request)
	{
		$this->validate($request, [
			'password' => 'required|min:6|max:191',
			'newPassword' => 'required|min:6|max:191'
		]);

		try {
			$user = Auth::user();
			if (Hash::check($request->input('password'), $user->password)) {
				if ($user->update(['password' => app('hash')->make($request->input('newPassword'))])) {
					// Send notification email to user
					if ($user->email) {
						$user->notify(new InformedNotification('Password has been updated successfully.'));
					}

					return $this->returnSuccessMessage('user', new UserResource($user));
				}

				// Send error if there is an error on update user password
				return $this->returnError('user password', 503, 'update');
			}

			return $this->returnErrorMessage(403, 'Invalid password.');
		} catch (Exception $e) {
			// Send error
			return $this->returnErrorMessage(503, $e->getMessage());
		}
	}
}