<?php

namespace App\Http\Controllers;

use Auth;
use App\Models\Application;
use App\Models\ApplicationUser;
use App\Http\Resources\UserResource;
use App\Http\Resources\ApplicationResource;
use App\Http\Resources\ApplicationUserResource;
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
        return $this->returnSuccessMessage('applications', ApplicationResource::collection($applications));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|max:191'
        ]);

        // Check whether user is SuperAdmin or not
        $user = Auth::user();
        if ($user->role != "SuperAdmin") {
	        return $this->returnErrorMessage('application', 403, 'create');
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

	        return $this->returnSuccessMessage('application', new ApplicationResource($application));
        }

        // Send error if application is not created
	    return $this->returnErrorMessage('application', 503, 'create');
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $application = Auth::user()->applications()->where('application_id', $id)->first();
        if ($application) {
	        return $this->returnSuccessMessage('application', new ApplicationResource($application));
        }

	    // Send error if application does not exist
	    return $this->returnErrorMessage('application', 404, 'show');
    }

    /**
     * Get users for the Application.
     *
     * @param  int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUsers($id)
    {
	    $user = Auth::user();
	    $application = $user->applications()->where('application_id', $id)->first();

        if ($application) {
            // Check whether user has permission to get
            $user = Auth::user();
            if (!$this->hasPermission($user, $application)) {
	            return $this->returnErrorMessage('application', 403, 'see the users of');
            }

            $users = $application->users()->get();
	        return $this->returnSuccessMessage('users', UserResource::collection($users));
        }

        // Send error if application does not exist
	    return $this->returnErrorMessage('application', 404, 'show users');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int $id
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
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
	        return $this->returnErrorMessage('application', 404, 'update');
        }

        // Check whether user has permission to update
	    if (!$this->hasPermission($user, $application)) {
		    return $this->returnErrorMessage('application', 403, 'update');
	    }

        // Update application
        if ($application->fill($request->all())->save()) {
	        return $this->returnSuccessMessage('application', new ApplicationResource($application));
        }

        // Send error if there is an error on update
	    return $this->returnErrorMessage('application', 503, 'update');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $user = Auth::user();
	    $application = $user->applications()->where('application_id', $id)->first();

        // Check whether user has permission to delete
	    if (!$this->hasPermission($user, $application)) {
		    return $this->returnErrorMessage('application', 403, 'delete');
	    }

	    // Delete Application
        if ($application->delete()) {
	    	return $this->returnSuccessMessage('message', 'Application has been deleted successfully.');
        }

	    // Send error if there is an error on update
	    return $this->returnErrorMessage('application', 503, 'delete');
    }

    /**
     * Get Application invitation token.
     *
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getInvitationToken($id)
    {
	    $user = Auth::user();
	    $application = $user->applications()->where('application_id', $id)->first();

	    // Send error if application does not exist
	    if (!$application) {
		    return $this->returnErrorMessage('application', 404, 'get invitation token');
	    }

        // Check whether user has permission to get invitation token
        if (!$this->hasPermission($user, $application)) {
	        return $this->returnErrorMessage('application', 403, 'get invitation token');
        }

	    return $this->returnSuccessMessage('shareToken', $application->share_token);
    }

	/**
	 * Invite users to the Application.
	 *
	 * @param $id
	 * @param  \Illuminate\Http\Request $request
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function inviteUsers($id, Request $request)
	{
		$user = Auth::user();
		$application = $user->applications()->where('application_id', $id)->first();

		// Send error if application does not exist
		if (!$application) {
			return $this->returnErrorMessage('application', 404, 'send invitation');
		}

		// Check whether user has permission to send invitation
		if (!$this->hasPermission($user, $application)) {
			return $this->returnErrorMessage('application', 403, 'send invitation');
		}

		// Send invitation
		$invitations = $request->input('invitations', []);
		$this->sendInvitation('application', $application, $invitations);

		return $this->returnSuccessMessage('message', 'Invitation has been sent successfully.');
	}

    /**
     * Accept invitation request.
     *
     * @param $token
     * @return \Illuminate\Http\JsonResponse
     */
    public function invitation($token)
    {
        return $this->acceptInvitation('application', $token);
    }

    /**
     * Join to the Application.
     *
     * @param $token
     * @return \Illuminate\Http\JsonResponse
     */
    public function join($token)
    {
	    return $this->acceptJoin('application', $token);
    }

	/**
	 * Update user role in the Application.
	 *
	 * @param $application_id
	 * @param $id
	 * @param Request $request
	 *
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function updateUser($application_id, $id, Request $request)
	{
		$user = Auth::user();
		$application = $user->applications()->where([
			'application_id' => $application_id
		])->first();

		// Send error if application does not exist
		if (!$application) {
			return $this->returnErrorMessage('application', 404, 'update user');
		}

		$applicationUser = ApplicationUser::where([
			'user_id' => $id,
			'application_id' => $application->id
		])->first();

		// Send error if user does not exist in the team
		if (!$applicationUser) {
			return $this->returnErrorMessage('user', 404, 'update role');
		}

		// Check whether user has permission to delete
		if (!$this->hasPermission($user, $application)) {
			return $this->returnErrorMessage('application', 403, 'update user');
		}

		$applicationUser->role = $request->input('role');
		if ($applicationUser->save()) {
			return $this->returnSuccessMessage('user', new ApplicationUserResource($applicationUser));
		}

		// Send error if there is an error on update
		return $this->returnErrorMessage('user role', 503, 'update');
	}

	/**
	 * Check whether user has permission or not
	 *
	 * @param $user
	 * @param $application
	 *
	 * @return bool
	 */
    protected function hasPermission($user, $application)
    {
	    $role = ApplicationUser::where([
		    'user_id' => $user->id,
		    'application_id' => $application->id
	    ])->value('role');

	    if ($user->role != "SuperAdmin" && $role != "Admin") {
		    return false;
	    }

	    return true;
    }
}
