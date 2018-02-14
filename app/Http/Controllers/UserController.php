<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Hash;

use Illuminate\Http\Request;

use App\User;

class UserController extends Controller
{
    public function __construct()
    {
        // $this->middleware('auth:api');
    }

    public function login(Request $request)
    {
        $this->validate($request, [
            'email' => 'required',
            'password' => 'required'
        ]);
        $user = User::where('email', $request->input('email'))->first();
        if($user && Hash::check($request->input('password'), $user->password)){
            $apikey = base64_encode(str_random(40));
            User::where('email', $request->input('email'))->update(['api_key' => $apikey]);
            return response()->json(['status' => 'success','api_key' => $apikey]);
        } else {
            return response()->json(['status' => 'fail'], 401);
        }
    }

    public function logout(Request $request)
    {
        $this->validate($request, [
            'email' => 'required',
            'password' => 'required'
        ]);
        $user = User::where('email', $request->input('email'))->first();
        if($user && Hash::check($request->input('password'), $user->password)){
            $apikey = base64_encode(str_random(40));
            User::where('email', $request->input('email'))->update(['api_key' => $apikey]);
            return response()->json(['status' => 'success','api_key' => $apikey]);
        } else {
            return response()->json(['status' => 'fail'], 401);
        }
    }

    public function register(Request $request)
    {
    }

    public function resetpassword(Request $request)
    {
    }
}