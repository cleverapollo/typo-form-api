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
//        $this->middleware('auth:api')->except(['logout']);
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
	 * @param  $provider
	 */
	public function handleProviderCallback($provider)
	{
		$providerUser = Socialite::driver($provider)->stateless()->user();

		$authUser = $this->findOrCreateUser($providerUser, $provider);
		Auth::login($authUser, true);
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
			'first_name' => $providerUser->getName(),
			'last_name' => null,
			'email' => $providerUser->getEmail(),
			'password' => null,
			'role_id' => Role::where('name', 'User')->first()->id
		]);
	}
}
