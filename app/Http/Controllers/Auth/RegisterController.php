<?php

namespace App\Http\Controllers\Auth;

use \Exception;
use \RoleRepository;
use \UserRepository;
use App\Models\Role;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Laravel\Lumen\Routing\Controller;

class RegisterController extends Controller
{
    /**
     * Handle a registration request for the application.
     *
     * @param  \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function register(Hash $hash, Request $request)
    {
        $input = $this->validate($request, [
            'first_name' => 'required|string|max:191',
            'last_name' => 'required|string|max:191',
            'email' => 'required|email|max:191',
            'password' => 'required|string|min:10|regex:/^(?=.*?[a-z])(?=.*?[A-Z])(?=.*\d)(?=.*?[[:punct:] ]).*$/'
        ]);

        try {
            $email = strtolower($input['email']);
            $roleId = RoleRepository::idByName('User');
            $user = User::whereEmail($email)->first();

            if($user) {
                // If the user already exists, most likely from the invite flow, and the user is
                // unregisted (essentially, they have never signed in before), they can update
                // some core details. It's important this only happens once, we don't want
                // anyone just typing an email address and updating a users password!
                //
                // `registerUser` will throw if the user is already registered. We are catching this
                //  within the controller. Unlike most api endpoints where we centralise error 
                // handling, if anything goes wrong in the register flow we want to return a 
                // pretty standard, non-descript error message
                //
                $user = UserRepository::registerUser($user, $input['first_name'], $input['last_name'], $input['password']);
            } else {
                // The user doesn't exist, we will create one now. This matches the existing flow
                // of allowing a user to register even when no apps are assigned to them
                //
                $user = UserRepository::createRegisteredUser($input['first_name'], $input['last_name'], $email, $roleId, $input['password']);
            }

            return response()->json(['message' => __('responses.register_200')], 200);

        } catch (\Throwable $e) {
            return response()->json(['message' => __('responses.register_503')], 503);
        }
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
}
