<?php

namespace App\Http\Controllers;

use Auth;
use App\Http\Resources\UserResource;
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
     * Display the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function show()
    {
        $user = Auth::user();
        return response()->json([
            'status' => 'success',
            'user' => new UserResource($user)
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $this->validate($request, [
            'first_name' => 'filled',
            'last_name' => 'filled',
            'email' => 'filled'
        ]);

        $user = Auth::user();
        if ($user->fill($request->all())->save()) {
            return response()->json([
                'status' => 'success',
                'user' => new UserResource($user)
            ], 200);
        }
        return response()->json([
            'status' => 'fail',
            'message' => $this->generateErrorMessage('user', 503, 'update')
        ], 503);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy()
    {
        if (Auth::user()->delete()) {
            return response()->json(['status' => 'success'], 200);
        }
        return response()->json([
            'status' => 'fail',
            'message' => $this->generateErrorMessage('user', 503, 'delete')
        ], 503);
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
            'email' => 'required',
            'password' => 'required'
        ]);

        $user = Auth::user();
        if (Hash::check($request->input('password'), $user->password)) {
            if ($user->update(['email' => $request->input('email')])) {
                return response()->json([
                    'status' => 'success',
                    'user' => new UserResource($user)
                ], 200);
            }
            return response()->json([
                'status' => 'fail',
                'message' => $this->generateErrorMessage('user email', 503, 'update')
            ], 503);
        }
        return response()->json([
            'status' => 'fail',
            'message' => 'Invalid password.'
        ], 400);
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
            'password' => 'required|min:6',
            'newPassword' => 'required|min:6'
        ]);

        $user = Auth::user();
        if (Hash::check($request->input('password'), $user->password)) {
            if ($user->update(['password' => app('hash')->make($request->input('newPassword'))])) {
                return response()->json([
                    'status' => 'success',
                    'user' => new UserResource($user)
                ], 200);
            }
            return response()->json([
                'status' => 'fail',
                'message' => $this->generateErrorMessage('user password', 503, 'update')
            ], 503);
        }
        return response()->json([
            'status' => 'fail',
            'message' => 'Invalid password.'
        ], 400);
    }
}