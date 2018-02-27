<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use Illuminate\Contracts\Auth\Guard;
use App\User;
use Carbon\Carbon;

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
            $expire_date = Carbon::now();
            $user->update(['api_token' => $api_token, 'expire_date' => $expire_date]);
            return response()->json(['status' => 'success', 'user' => $user]);
        }

        return response()->json(['status' => 'fail'], 401);
    }

    public function userInfo(Request $request)
    {
        $user = User::where('api_token', $request->header('api_token'))->first();
        if ($user) {
            return response()->json(['status' => 'success', 'user' => $user]);
        }

        return response()->json(['status' => 'fail'], 401);
    }

    public function logout(Request $request)
    {
        $user = $this->auth->user();

        $user->api_token = NULL;
        $user->expire_date = NULL;
        $user->save();

        return response()->json(['message' => 'success'], 200);
    }

    public function register(Request $request)
    {
        $this->validate($request, [
            'first_name' => 'required',
            'last_name' => 'required',
            'email' => 'required|unique:users|email',
            'password' => 'required'
        ]);

        $user = User::create(['first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'password' => app('hash')->make($request->password)
        ]);

        if ($user) {
            return response()->json(['status' => 'success'], 200);
        }

        return response()->json(['status' => 'fail']);
    }

    public function resetPassword(Request $request)
    {
    }
}