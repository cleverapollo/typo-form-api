<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use App\User;
use Carbon\Carbon;
use Auth;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => [
            'login', 'register'
        ]]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $user = User::get();
        return response()->json(['status' => 'success', 'result' => $user]);
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
        $user = Auth::user();
        if ($user) {
            return response()->json(['status' => 'success', 'user' => $user]);
        }

        return response()->json(['status' => 'fail'], 401);
    }

    public function logout(Request $request)
    {
        $user = Auth::user();

        $user->api_token = null;
        $user->expire_date = null;
        $user->save();

        return response()->json(['message' => 'success'], 200);
    }

    public function register(Request $request)
    {
        $this->validate($request, [
            'first_name' => 'required|max:255',
            'last_name' => 'required|max:255',
            'email' => 'required|unique:users|email',
            'password' => 'required'
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
            'email' => 'filled'
        ]);

        $user = User::find($id);
        if ($user->fill($request->all())->save()) {
            return response()->json(['status' => 'success'], 200);
        }

        return response()->json(['status' => 'fail']);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function updateEmail(Request $request)
    {
        $this->validate($request, [
            'password' => 'required',
            'email' => 'required'
        ]);

        $user = Auth::user();
        if ($user && Hash::check($request->input('password'), $user->password)) {
            $user->update(['email' => $request->input('email')]);
            return response()->json(['status' => 'success', 'user' => $user]);
        }

        return response()->json(['status' => 'fail']);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function updatePassword(Request $request)
    {
        $this->validate($request, [
            'password' => 'required',
            'newPassword' => 'required'
        ]);

        $user = Auth::user();
        if ($user && Hash::check($request->input('password'), $user->password)) {
            $user->update(['password' => app('hash')->make($request->input('newPassword'))]);
            return response()->json(['status' => 'success', 'user' => $user]);
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