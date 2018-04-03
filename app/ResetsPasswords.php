<?php

namespace App;

use Illuminate\Http\Request;
use Illuminate\Mail\Message;
use Illuminate\Support\Facades\Password;

trait ResetsPasswords
{
	/**
	 * Send a reset link to the given user.
	 *
	 * @param  \Illuminate\Http\Request $request
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function sendResetLinkEmail(Request $request)
	{
		$this->validate($request, ['email' => 'required|email']);

		$broker = $this->getBroker();

		$response = Password::broker($broker)->sendResetLink($request->only('email'), function (Message $message) {
			$message->subject($this->getEmailSubject());
		});

		switch ($response) {
			case Password::RESET_LINK_SENT:
				return $this->getSendResetLinkEmailSuccessResponse($response);

			case Password::INVALID_USER:
				return $this->getSendResetLinkEmailFailureResponse($response, "Invalid User");

			default:
				return $this->getSendResetLinkEmailFailureResponse($response, "Server Error");
		}
	}

	/**
	 * Get the e-mail subject line to be used for the reset link email.
	 *
	 * @return string
	 */
	protected function getEmailSubject()
	{
		return property_exists($this, 'subject') ? $this->subject : 'Your Password Reset Link';
	}

	/**
	 * Get the response for after the reset link has been successfully sent.
	 *
	 * @param  string $response
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	protected function getSendResetLinkEmailSuccessResponse($response)
	{
		return response()->json(['status' => 'success'], 200);
	}

	/**
	 * Get the response for after the reset link could not be sent.
	 *
	 * @param  string $response
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	protected function getSendResetLinkEmailFailureResponse($response, $message)
	{
		return response()->json(['status' => 'fail', 'message' => $message], 503);
	}

	/**
	 * Reset the given user's password.
	 *
	 * @param  \Illuminate\Http\Request $request
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function reset(Request $request)
	{
		$this->validate($request, $this->getResetValidationRules());

		$credentials = $request->only(
			'email', 'password', 'password_confirmation', 'token'
		);

		$broker = $this->getBroker();

		$response = Password::broker($broker)->reset($credentials, function ($user, $password) {
			$this->resetPassword($user, $password);
		});

		switch ($response) {
			case Password::PASSWORD_RESET:
				return $this->getResetSuccessResponse($response);

			default:
				return $this->getResetFailureResponse($request, $response);
		}
	}

	/**
	 * Get the password reset validation rules.
	 *
	 * @return array
	 */
	protected function getResetValidationRules()
	{
		return [
			'token' => 'required',
			'email' => 'required|email',
			'password' => 'required|confirmed|min:6',
		];
	}

	/**
	 * Reset the given user's password.
	 *
	 * @param  \Illuminate\Contracts\Auth\CanResetPassword $user
	 * @param  string $password
	 * @return \Illuminate\Http\JsonResponse
	 */
	protected function resetPassword($user, $password)
	{
		$user->password = app('hash')->make($password);

		$user->save();

		return response()->json(['status' => 'success'], 200);
	}

	/**
	 * Get the response for after a successful password reset.
	 *
	 * @param  string $response
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	protected function getResetSuccessResponse($response)
	{
		return response()->json(['status' => 'success'], 200);
	}

	/**
	 * Get the response for after a failing password reset.
	 *
	 * @param  Request $request
	 * @param  string $response
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	protected function getResetFailureResponse(Request $request, $response)
	{
		return response()->json(['status' => 'fail']);
	}

	/**
	 * Get the broker to be used during password reset.
	 *
	 * @return string|null
	 */
	public function getBroker()
	{
		return property_exists($this, 'broker') ? $this->broker : null;
	}
}