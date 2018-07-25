<?php

namespace App\Http\Foundation\Auth;

use App\User;
use Carbon\Carbon;
use App\Models\Throttle;
use App\Http\Resources\AuthResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

trait AuthenticatesUsers
{
	use RedirectsUsers, ThrottlesLogins;

	/**
	 * Show the application's login form.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function showLoginForm()
	{
		return view('auth.login');
	}

	/**
	 * Handle a login request to the application.
	 *
	 * @param  \Illuminate\Http\Request $request
	 *
	 * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\Response|\Illuminate\Http\JsonResponse
	 */
	public function login(Request $request)
	{
		$this->validateLogin($request);

        $throttle = Throttle::where([
            ["email", "=", $request->input("email")],
            ["created_at", ">", Carbon::now()->subMinutes(5)]
        ])->get();

        if (!is_null($throttle) && count($throttle) == 5) {
            $this->fireLockoutEvent($request);

            return $this->sendLockoutResponse($request);
        }

        // If the class is using the ThrottlesLogins trait, we can automatically throttle
		// the login attempts for this application. We'll key this by the username and
		// the IP address of the client making these requests into this application.
		if ($this->hasTooManyLoginAttempts($request)) {
			$this->fireLockoutEvent($request);

			return $this->sendLockoutResponse($request);
		}

		$user = $this->attemptLogin($request);
		if ($user) {
			return $this->sendLoginResponse($request, $user);
		}

		// If the login attempt was unsuccessful we will increment the number of attempts
		// to login and redirect the user back to the login form. Of course, when this
		// user surpasses their maximum number of attempts they will get locked out.
		$this->incrementLoginAttempts($request);

		return $this->sendFailedLoginResponse($request);
	}

	/**
	 * Validate the user login request.
	 *
	 * @param  \Illuminate\Http\Request $request
	 *
	 * @return void
	 */
	protected function validateLogin(Request $request)
	{
		$this->validate($request, [
			$this->username() => 'required|email',
			//'password' => 'required|string|min:10|max:191|regex:/^.*(?=.{3,})(?=.*[a-zA-Z])(?=.*[0-9])(?=.*[\d\X])(?=.*[!$#%]).*$/',
			'password' => 'required|string',
			//'g-recaptcha-response' => 'required|recaptcha'
		]);
	}

	/**
	 * Attempt to log the user into the application.
	 *
	 * @param Request $request
	 * @return AuthResource|null
	 */
	protected function attemptLogin(Request $request)
	{
		$user = User::where('email', $request->input('email'))->first();
		if ($user && Hash::check($request->input('password'), $user->password)) {
			$api_token = base64_encode(str_random(40));
			while (!is_null(User::where('api_token', $api_token)->first())) {
				$api_token = base64_encode(str_random(40));
			}
			$expire_date = Carbon::now();
			$user->update(['api_token' => $api_token, 'expire_date' => $expire_date]);
			return new AuthResource($user);
		}

		return null;
	}

	/**
	 * Get the needed authorization credentials from the request.
	 *
	 * @param  \Illuminate\Http\Request $request
	 *
	 * @return array
	 */
	protected function credentials(Request $request)
	{
		return $request->only($this->username(), 'password');
	}

	/**
	 * Send the response after the user was authenticated.
	 *
	 * @param  \Illuminate\Http\Request $request
	 * @param  $user
	 *
	 * @return \Illuminate\Http\Response
	 */
	protected function sendLoginResponse(Request $request, $user)
	{
		$this->clearLoginAttempts($request);

		return response()->json(['status' => 'success', 'user' => $user], 200);
	}

	/**
	 * The user has been authenticated.
	 *
	 * @param  \Illuminate\Http\Request $request
	 * @param  mixed $user
	 *
	 * @return mixed
	 */
	protected function authenticated(Request $request, $user)
	{
		//
	}

	/**
	 * Get the failed login response instance.
	 *
	 * @param  \Illuminate\Http\Request $request
	 *
	 * @return \Illuminate\Http\Response
	 */
	protected function sendFailedLoginResponse(Request $request)
	{
		return response()->json([
			'status' => 'fail',
			'message' => 'Invalid email or password.'
		], 401);
	}

	/**
	 * Get the login username to be used by the controller.
	 *
	 * @return string
	 */
	public function username()
	{
		return 'email';
	}

	/**
	 * Log the user out of the application.
	 *
	 * @param  \Illuminate\Http\Request $request
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function logout(Request $request)
	{
		$user = Auth::user();
		if (!$user) {
			$user = User::where('api_token', $request->input('api_token'))->first();
		}
		$user->api_token = null;
		$user->expire_date = null;
		$user->save();

		return response()->json(['status' => 'success'], 200);
	}

	/**
	 * Get the guard to be used during authentication.
	 *
	 * @return \Illuminate\Contracts\Auth\StatefulGuard
	 */
	protected function guard()
	{
		return Auth::guard();
	}
}
