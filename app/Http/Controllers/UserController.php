<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use Illuminate\Contracts\Auth\Guard;
use App\User;

class UserController extends Controller
{
    private $auth;

    public function __construct(Guard $auth)
    {
        $this->middleware('auth:api', ['except' => [
            'login', 'register'
        ]]);

        $this->auth = $auth;
    }

    public function login(Request $request)
    {
        $this->validate($request, [
            'email' => 'required',
            'password' => 'required'
        ]);
        $user = User::where('email', $request->input('email'))->first();
        if ($user && Hash::check($request->input('password'), $user->password)) {
            $api_token = base64_encode(str_random(40));
            User::where('email', $request->input('email'))->update(['api_token' => $api_token]);
            return response()->json(['status' => 'success', 'api_token' => $api_token]);
        } else {
            return response()->json(['status' => 'fail'], 401);
        }
    }

    public function logout(Request $request)
    {
        $user = $this->auth->user();

        $user->api_token= '';
        $user->save();

        return response()->json(['message'=>'success'], 200);
    }

    public function register(Request $request)
    {
        $this->validate($request, [
            'name' => 'required',
            'email' => 'required|unique:users|email',
            'password' => 'required'
        ]);

        $user = User::create(['name' => $request->name,
            'email' => $request->email,
            'password' => app('hash')->make($request->password)
        ]);

        if($user) {
            return response()->json(['status' => 'success'], 200);
        }
    }

    public function resetpassword(Request $request)
    {
    }
}