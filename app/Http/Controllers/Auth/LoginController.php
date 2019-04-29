<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Http\Foundation\Auth\AuthenticatesUsers;
use App\Services\ApplicationService;

class LoginController extends Controller
{
    private $applicationService;
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->applicationService = new ApplicationService;
    }
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

    /**
     * Handle an authentication attempt.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  mixed $user
     *
     * @return void
     */
    public function authenticated(Request $request, $user)
    {
        $origin = $request->header('Origin');
        if (strlen($origin)) {
            $request_slug = explode('.', explode('://', $origin)[1])[0];
            $this->applicationService->acceptInvitation($request_slug, $user);
        }
    }
}
