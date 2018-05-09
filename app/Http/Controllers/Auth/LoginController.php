<?php

namespace App\Http\Controllers\Auth;

use App\User;
use App\Models\Role;
use Laravel\Socialite\Facades\Socialite;
use App\Http\Controllers\Controller;
use App\Http\Foundation\Auth\AuthenticatesUsers;

class LoginController extends Controller
{
	/*
	|--------------------------------------------------------------------------
	| Login Controller
	|--------------------------------------------------------------------------
	|
	| This controller handles authenticating users for the application and
	| redirecting them to your home screen. The controller uses a trait
	| to conveniently provide its functionality to your applications.
	|
	*/

	use AuthenticatesUsers;

//    /**
//     * Where to redirect users after login.
//     *
//     * @var string
//     */
//    protected $redirectTo = '/home';

//    /**
//     * Create a new controller instance.
//     *
//     * @return void
//     */
//    public function __construct()
//    {
//        $this->middleware('auth:api')->except(['logout', 'redirectToProvider', 'handleProviderCallback']);
//    }

	/**
	 * Redirect the user to the Facebook authentication page.
	 *
	 * @param  $provider
	 * @return \Illuminate\Http\Response
	 */
	public function redirectToProvider($provider)
	{
		/**
		 * https://github.com/sahat/satellizer
		 */
		return Socialite::driver($provider)->stateless()->redirect();
	}

	/**
	 * Obtain the user information from provider.
	 *
	 * @param $provider
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function handleProviderCallback($provider)
	{
		$providerUser = Socialite::driver($provider)->stateless()->user();

		$authUser = $this->findOrCreateUser($providerUser, $provider);
		return response()->json(['status' => 'success', 'user' => $authUser], 200);
	}

	/**
	 * Create or find user from provider
	 *
	 * @param  $providerUser
	 * @param  $provider
	 * @return mixed
	 */
	protected function findOrCreateUser($providerUser, $provider)
	{
		$authUser = User::where('email', $providerUser->getEmail())->first();

		if (!empty($authUser)) {
			return $authUser;
		}

		return User::create([
			'first_name' => $providerUser->getName() || '',
			'last_name' => '',
			'email' => $providerUser->getEmail(),
			'password' => '',
			'role_id' => Role::where('name', 'User')->first()->id,
			'api_token' => $providerUser->token,
			'expire_date' => $providerUser->expiresIn
		]);
	}
}
