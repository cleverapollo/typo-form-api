<?php

namespace App\Http\Controllers;

use Auth;
use App\Models\Application;
use App\Models\ApplicationUser;
use App\Http\Resources\UserResource;
use App\Http\Resources\ApplicationResource;
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
            'applications' => ApplicationResource::collection($applications)
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

        // Check whether user is SuperAdmin or not
        $user = Auth::user();
        if ($user->role != "SuperAdmin") {
            return response()->json([
                'status' => 'fail',
                'message' => 'You do not have permission to create application.'
            ], 403);
        }

        $share_token = base64_encode(str_random(40));
        while (!is_null(Application::where('share_token', $share_token)->first())) {
            $share_token = base64_encode(str_random(40));
        }

        // Create application
        $application = $user->applications()->create([
            'name' => $request->input('name'),
            'share_token' => $share_token
        ]);
        if ($application) {
            // Send invitation
            $invitations = $request->input('invitations', []);
            $this->sendInvitation('application', $application, $invitations);

            return response()->json([
                'status' => 'success',
                'application' => new ApplicationResource($application)
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
                'application' => new ApplicationResource($application)
            ], 200);
        }

        return response()->json([
            'status' => 'fail',
            'message' => $this->generateErrorMessage('application', 404, 'show')
        ], 404);
    }

    /**
     * Get users for the Application.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function getUsers($id)
    {
        $application = Application::find($id);
        if ($application) {
            // Check whether user have permission to get
            $user = Auth::user();
            $role = ApplicationUser::where([
                'user_id' => $user->id,
                'application_id' => $application->id
            ])->value('role');
            if ($user->role != "SuperAdmin" && $role != "Admin") {
                return response()->json([
                    'status' => 'fail',
                    'message' => 'You do not have permission to see the users of application.'
                ], 403);
            }

            $users = $application->users()->get();
            return response()->json([
                'status' => 'success',
                'users' => UserResource::collection($users)
            ], 200);
        }

        return response()->json([
            'status' => 'fail',
            'message' => $this->generateErrorMessage('application', 404, 'show users')
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
                'message' => 'You do not have permission to update application.'
            ], 403);
        }

        // Update application
        if ($application->fill($request->all())->save()) {
            return response()->json([
                'status' => 'success',
                'application' => new ApplicationResource($application)
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

        // Check whether user have permission to delete
        $role = ApplicationUser::where([
            'user_id' => $user->id,
            'application_id' => $application->id
        ])->value('role');
        if ($user->role != "SuperAdmin" && $role != "Admin") {
            return response()->json([
                'status' => 'fail',
                'message' => 'You do not have permission to delete application.'
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

    /**
     * Get Application invitation token.
     *
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getInvitationToken($id)
    {
        $application = Application::find($id);
        if ($application) {
            $user = Auth::user();

            // Check whether user have permission to get invitation token
            $role = ApplicationUser::where([
                'user_id' => $user->id,
                'application_id' => $application->id
            ])->value('role');
            if ($user->role != "SuperAdmin" && $role != "Admin") {
                return response()->json([
                    'status' => 'fail',
                    'message' => 'You do not have permission to get invitation token.'
                ], 403);
            }

            return response()->json([
                'status' => 'success',
                'shareToken' => $application->share_token
            ], 200);
        }
        return response()->json([
            'status' => 'fail',
            'message' => $this->generateErrorMessage('application', 404, 'get invitation token')
        ], 404);
    }

    /**
     * Accept invitation request.
     *
     * @param $token
     */
    public function invitation($token)
    {
        $this->acceptInvitation('application', $token);
    }

    /**
     * Join to the Application.
     *
     * @param $token
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function join($token, Request $request)
    {

    }
}
