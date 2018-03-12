<?php

namespace App\Http\Controllers;

use Auth;
use App\Models\Application;
use App\Models\ApplicationUser;
use Illuminate\Http\Request;

class ApplicationController extends Controller
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
        $applications = Auth::user()->applications()->get();
        return response()->json([
            'status' => 'success',
            'applications' => $applications
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|max:191'
        ]);

        $user = Auth::user();

        // Check whether user is SuperAdmin or not
        if ($user->role != "SuperAdmin") {
            return response()->json([
                'status' => 'fail',
                'message' => 'You do not have permission to create application.'
            ], 403);
        }

        // Create application
        $application = $user->applications()->create($request->only(['name']));
        if ($application) {
            // Send invitation
            $invitations = $request->input('invitations', []);
            $this->sendInvitation('application', $application, $user, $invitations);

            return response()->json([
                'status' => 'success',
                'application' => $application
            ], 200);
        }

        // Send error if application is not created
        return response()->json([
            'status' => 'fail',
            'message' => $this->generateErrorMessage('application', 503, 'store')
        ], 503);
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $application = Auth::user()->applications()->where('application_id', $id)->first();
        if ($application) {
            return response()->json([
                'status' => 'success',
                'application' => $application
            ], 200);
        }

        return response()->json([
            'status' => 'fail',
            'message' => $this->generateErrorMessage('application', 404, 'show')
        ], 404);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int $id
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function update($id, Request $request)
    {
        $this->validate($request, [
            'name' => 'filled'
        ]);

        $user = Auth::user();
        $application = $user->applications()->where('application_id', $id)->first();

        // Send error if application does not exist
        if (!$application) {
            return response()->json([
                'status' => 'fail',
                'message' => $this->generateErrorMessage('application', 404, 'update')
            ], 404);
        }

        // Check whether user have permission to update
        $role = ApplicationUser::where([
            'user_id' => $user->id,
            'application_id' => $application->id
        ])->value('role');
        if ($user->role != "SuperAdmin" && $role != "Admin") {
            return response()->json([
                'status' => 'fail',
                'message' => 'You do not have permission to create application.'
            ], 403);
        }

        // Update application
        if ($application->fill($request->all())->save()) {
            return response()->json([
                'status' => 'success',
                'application' => $application
            ], 200);
        }

        // Send error if there is an error on update
        return response()->json([
            'status' => 'fail',
            'message' => $this->generateErrorMessage('application', 503, 'update')
        ], 503);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $user = Auth::user();
        $application = Application::find($id);

        // Check whether user have permission to update
        $role = ApplicationUser::where([
            'user_id' => $user->id,
            'application_id' => $application->id
        ])->value('role');
        if ($user->role != "SuperAdmin" && $role != "Admin") {
            return response()->json([
                'status' => 'fail',
                'message' => 'You do not have permission to create application.'
            ], 403);
        }

        if ($application->delete()) {
            return response()->json(['status' => 'success'], 200);
        }
        return response()->json([
            'status' => 'fail',
            'message' => $this->generateErrorMessage('application', 503, 'delete')
        ], 503);
    }
}
