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
     * @return \Illuminate\Http\JsonResponse
     */
    public function show()
    {
        $user = Auth::user();
	    return $this->returnSuccessMessage('user', new UserResource($user));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request)
    {
        $this->validate($request, [
            'first_name' => 'filled',
            'last_name' => 'filled',
            'email' => 'filled'
        ]);

        $user = Auth::user();
        if ($user->fill($request->only('first_name', 'last_name', 'email'))->save()) {
	        return $this->returnSuccessMessage('user', new UserResource($user));
        }

	    // Send error if user is not updated
	    return $this->returnErrorMessage('user', 503, 'update');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy()
    {
        if (Auth::user()->delete()) {
	        return $this->returnSuccessMessage('message', 'User has been deleted successfully.');
        }

	    // Send error if there is an error on delete
	    return $this->returnErrorMessage('user', 503, 'delete');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
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
	            return $this->returnSuccessMessage('user', new UserResource($user));
            }

	        // Send error if there is an error on update user email
	        return $this->returnErrorMessage('user email', 503, 'update');
        }

        return response()->json([
            'status' => 'fail',
            'message' => 'Invalid password.'
        ], 403);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
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
	            return $this->returnSuccessMessage('user', new UserResource($user));
            }

	        // Send error if there is an error on update user password
	        return $this->returnErrorMessage('user password', 503, 'update');
        }

	    return response()->json([
		    'status' => 'fail',
		    'message' => 'Invalid password.'
	    ], 403);
    }
}