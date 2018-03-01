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
//        parent::__construct();

        $this->middleware('auth:api', ['except' => [
            'login', 'register'
        ]]);

        $this->auth = $auth;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $user = User::get();
        return response()->json(['status' => 'success', 'result' => $user], 200);
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

            return response()->json(['status' => 'success', 'user' => $user], 200);
        }

        return response()->json(['status' => 'fail'], 401);
    }

    public function userInfo(Request $request)
    {
        $user = User::where('api_token', $request->header('api_token'))->first();
        if ($user) {
            return response()->json(['status' => 'success', 'user' => $user], 200);
        }

        return response()->json(['status' => 'fail'], 401);
    }

    public function logout(Request $request)
    {
        $user = $this->auth->user();

        $user->api_token = null;
        $user->expire_date = null;
        $user->save();

        return response()->json(['status' => 'success'], 200);
    }

    public function register(Request $request)
    {
        $this->validate($request, [
            'first_name' => 'required|max:255',
            'last_name' => 'required|max:255',
            'email' => 'required|unique:users|email',
            'password' => 'required|min:6'
        ]);

        $user = User::create([
            'first_name' => $request->first_name,
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

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $user = User::where('id', $id)->get();
        return response()->json($user);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'first_name' => 'filled',
            'last_name' => 'filled',
            'email' => 'filled',
            'password' => 'filled'
        ]);

        $user = User::find($id);
        if ($user->fill($request->all())->save()) {
            return response()->json(['status' => 'success'], 200);
        }

        return response()->json(['status' => 'fail']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if (User::destroy($id)) {
            return response()->json(['status' => 'success'], 200);
        }

        return response()->json(['status' => 'fail']);
    }
}