<?php

namespace App\Http\Controllers;

use Auth;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:api');
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

    public function userInfo(Request $request)
    {
        $user = Auth::user();
        if ($user) {
            return response()->json(['status' => 'success', 'user' => $user], 200);
        }

        return response()->json(['status' => 'fail'], 401);
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
            return response()->json(['status' => 'success', 'user' => $user], 200);
        }

        return response()->json(['status' => 'fail'], 401);
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

        return response()->json(['status' => 'fail'], 401);
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
            return response()->json(['status' => 'success', 'user' => $user], 200);
        }

        return response()->json(['status' => 'fail'], 401);
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
            return response()->json(['status' => 'success', 'user' => $user], 200);
        }

        return response()->json(['status' => 'fail'], 401);
    }
}