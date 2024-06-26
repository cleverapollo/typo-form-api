<?php

namespace App\Http\Foundation\Auth;

use App\Models\Throttle;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Cache\RateLimiter;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Support\Facades\Lang;
use Illuminate\Validation\ValidationException;

trait ThrottlesLogins
{
	/**
	 * Determine if the user has too many failed login attempts.
	 *
	 * @param  \Illuminate\Http\Request $request
	 *
	 * @return bool
	 */
	protected function hasTooManyLoginAttempts(Request $request)
	{
        return $this->limiter()->tooManyAttempts(
			$this->throttleKey($request), $this->maxAttempts()
		);
	}

	/**
	 * Increment the login attempts for the user.
	 *
	 * @param  \Illuminate\Http\Request $request
	 *
	 * @return void
	 */
	protected function incrementLoginAttempts(Request $request)
	{
		$this->limiter()->hit(
			$this->throttleKey($request), $this->decayMinutes()
		);

        $email = strtolower($request->input("email"));
        $user = User::where("email", $email)->first();

        $throttle = Throttle::create([
            "user_id" => !is_null($user) ? $user->id : null,
            "email" => $email,
            "ip_address" => $request->ip()
        ]);
	}

	/**
	 * Redirect the user after determining they are locked out.
	 *
	 * @param  \Illuminate\Http\Request $request
	 *
	 * @return \Illuminate\Http\JsonResponse
	 */
	protected function sendLockoutResponse(Request $request)
	{
		$seconds = $this->limiter()->availableIn(
			$this->throttleKey($request)
		);

		return response()->json([
			"status" => "fail",
			"message" => "Too many failed login attempts. Please try again in 5 minutes."
		], 423);
	}

	/**
	 * Clear the login locks for the given user credentials.
	 *
	 * @param  \Illuminate\Http\Request $request
	 *
	 * @return void
	 */
	protected function clearLoginAttempts(Request $request)
	{
		$this->limiter()->clear($this->throttleKey($request));
	}

	/**
	 * Fire an event when a lockout occurs.
	 *
	 * @param  \Illuminate\Http\Request $request
	 *
	 * @return void
	 */
	protected function fireLockoutEvent(Request $request)
	{
		event(new Lockout($request));
	}

	/**
	 * Get the throttle key for the given request.
	 *
	 * @param  \Illuminate\Http\Request $request
	 *
	 * @return string
	 */
	protected function throttleKey(Request $request)
	{
		return Str::lower($request->input($this->username())) . "|" . $request->ip();
	}

	/**
	 * Get the rate limiter instance.
	 *
	 * @return \Illuminate\Cache\RateLimiter
	 */
	protected function limiter()
	{
		return app(RateLimiter::class);
	}

	/**
	 * Get the maximum number of attempts to allow.
	 *
	 * @return int
	 */
	public function maxAttempts()
	{
		return property_exists($this, "maxAttempts") ? $this->maxAttempts : 5;
	}

	/**
	 * Get the number of minutes to throttle for.
	 *
	 * @return int
	 */
	public function decayMinutes()
	{
		return property_exists($this, "decayMinutes") ? $this->decayMinutes : 5;
	}
}
