<?php

namespace App\Http\Controllers;

use Auth;
use App\User;
use Exception;
use App\Models\Role;
use App\Models\Application;
use App\Models\ApplicationUser;
use App\Models\ApplicationInvitation;
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
	 *
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function store(Request $request)
	{
		$this->validate($request, [
			'name' => 'required|unique:applications|max:191',
			'invitations' => 'array',
			'invitations.*.email' => 'required|email',
			'invitations.*.application_role_id' => 'required|integer|min:2'
		]);

		try {
			// Check whether user is SuperAdmin or not
			$user = Auth::user();
			if ($user->role->name != 'Super Admin') {
				return $this->returnError('application', 403, 'create');
			}

			$share_token = base64_encode(str_random(40));
			while (!is_null(Application::where('share_token', $share_token)->first())) {
				$share_token = base64_encode(str_random(40));
			}

			$name = str_replace(' ', '_', $request->input('name'));
			if ($user->applications()->where('name', $name)->count() > 0) {
				return response()->json([
					'name' => ['The name has already been taken.']
				], 422);
			}

			// Create application
			$application = $user->applications()->create([
				'name' => $name,
				'share_token' => $share_token,
				'css' => $request->input('css', null)
			], [
				'role_id' => Role::where('name', 'Admin')->first()->id
			]);

			if ($application) {
				// Send invitation
				$invitations = $request->input('invitations', []);
				$this->sendInvitation('application', $application, $invitations);

				return $this->returnSuccessMessage('application', new ApplicationResource($application));
			}

			// Send error if application is not created
			return $this->returnError('application', 503, 'create');
		} catch (Exception $e) {
			// Send error
			return $this->returnErrorMessage(503, $e->getMessage());
		}
	}

	/**
	 * Display the specified resource.
	 *
	 * @param  int $id
	 *
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function show($id)
	{
		$application = Auth::user()->applications()->find($id);
		if ($application) {
			return $this->returnSuccessMessage('application', new ApplicationResource($application));
		}

		// Send error if application does not exist
		return $this->returnError('application', 404, 'show');
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  int $id
	 * @param  \Illuminate\Http\Request $request
	 *
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function update($id, Request $request)
	{
		$this->validate($request, [
			'name' => 'filled|max:191'
		]);

		try {
			$user = Auth::user();
			$application = $user->applications()->find($id);

			// Send error if application does not exist
			if (!$application) {
				return $this->returnError('application', 404, 'update');
			}

			// Check whether user has permission to update
			if (!$this->hasPermission($user, $application)) {
				return $this->returnError('application', 403, 'update');
			}

			// Update application
			if ($application->fill($request->only('name', 'css'))->save()) {
				return $this->returnSuccessMessage('application', new ApplicationResource($application));
			}

			// Send error if there is an error on update
			return $this->returnError('application', 503, 'update');
		} catch (Exception $e) {
			// Send error
			return $this->returnErrorMessage(503, $e->getMessage());
		}
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int $id
	 *
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function destroy($id)
	{
		try {
			$user = Auth::user();
			$application = $user->applications()->find($id);

			// Check whether user has permission to delete
			if (!$this->hasPermission($user, $application)) {
				return $this->returnError('application', 403, 'delete');
			}

			// Delete Application
			if ($application->delete()) {
				return $this->returnSuccessMessage('message', 'Application has been deleted successfully.');
			}

			// Send error if there is an error on update
			return $this->returnError('application', 503, 'delete');
		} catch (Exception $e) {
			// Send error
			return $this->returnErrorMessage(503, $e->getMessage());
		}
	}

	/**
	 * Get Application invitation token.
	 *
	 * @param  int $id
	 *
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function getInvitationToken($id)
	{
		$user = Auth::user();
		$application = $user->applications()->find($id);

		// Send error if application does not exist
		if (!$application) {
			return $this->returnError('application', 404, 'get invitation token');
		}

		// Check whether user has permission to get invitation token
		if (!$this->hasPermission($user, $application)) {
			return $this->returnError('application', 403, 'get invitation token');
		}

		return $this->returnSuccessMessage('shareToken', $application->share_token);
	}

	/**
	 * Accept invitation request.
	 *
	 * @param  string $token
	 *
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function invitation($token)
	{
		return $this->acceptInvitation('application', $token);
	}

	/**
	 * Join to the Application.
	 *
	 * @param  string $token
	 *
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function join($token)
	{
		return $this->acceptJoin('application', $token);
	}

	/**
	 * Get users for the Application.
	 *
	 * @param  string $application_name
	 *
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function getUsers($application_name)
	{
		$user = Auth::user();
		$application = $user->applications()->where('name', $application_name)->first();

		// Send error if application does not exist
		if (!$application) {
			return $this->returnApplicationNameError();
		}

		// Check whether user has permission to get
		$user = Auth::user();
		if (!$this->hasPermission($user, $application)) {
			return $this->returnError('application', 403, 'see the users of');
		}

		$currentUsers = $application->users()->get();

		$invitedUsers = ApplicationInvitation::where([
			'application_id' => $application->id,
			'status' => 0
		])->get();

		$unacceptedUsers = [];
		foreach ($invitedUsers as $invitedUser) {
			$unacceptedUser = User::where('email', $invitedUser->invitee)->first();
			if ($unacceptedUser) {
				array_push($unacceptedUsers, new UserResource($unacceptedUser));
			}
		}

		return $this->returnSuccessMessage('users', [
			'current' => UserResource::collection($currentUsers),
			'unaccepted' => $unacceptedUsers
		]);
	}

	/**
	 * Invite users to the Application.
	 *
	 * @param  string $application_name
	 * @param  \Illuminate\Http\Request $request
	 *
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function inviteUsers($application_name, Request $request)
	{
		$this->validate($request, [
			'invitations' => 'array',
			'invitations.*.email' => 'required|email',
			'invitations.*.application_role_id' => 'required|integer|min:2'
		]);

		$user = Auth::user();
		$application = $user->applications()->where('name', $application_name)->first();

		// Send error if application does not exist
		if (!$application) {
			return $this->returnApplicationNameError();
		}

		// Check whether user has permission to send invitation
		if (!$this->hasPermission($user, $application)) {
			return $this->returnError('application', 403, 'send invitation');
		}

		$invitations = $request->input('invitations', []);

		// Send invitation
		$this->sendInvitation('application', $application, $invitations);

		return $this->returnSuccessMessage('message', 'Invitation has been sent successfully.');
	}

	/**
	 * Update user role in the Application.
	 *
	 * @param  string $application_name
	 * @param  int $id
	 * @param  Request $request
	 *
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function updateUser($application_name, $id, Request $request)
	{
		$this->validate($request, [
			'application_role_id' => 'required|integer|min:2'
		]);

		try {
			// Check whether the role exists or not
			$role = Role::find($request->input('application_role_id'));
			if (!$role) {
				return $this->returnError('role', 404, 'update user');
			}

			$user = Auth::user();
			$application = $user->applications()->where('name', $application_name)->first();

			// Send error if application does not exist
			if (!$application) {
				return $this->returnApplicationNameError();
			}

			$applicationUser = ApplicationUser::where([
				'user_id' => $id,
				'application_id' => $application->id
			])->first();

			// Send error if user does not exist in the team
			if (!$applicationUser) {
				return $this->returnError('user', 404, 'update role');
			}

			// Check whether user has permission to delete
			if (!$this->hasPermission($user, $application)) {
				return $this->returnError('application', 403, 'update user');
			}

			// Update user role
			if ($applicationUser->fill(['role_id' => $role->id])->save()) {
				return $this->returnSuccessMessage('user', new ApplicationUserResource($applicationUser));
			}

			// Send error if there is an error on update
			return $this->returnError('user role', 503, 'update');
		} catch (Exception $e) {
			// Send error
			return $this->returnErrorMessage(503, $e->getMessage());
		}
	}

	/**
	 * Delete user from the Application.
	 *
	 * @param  string $application_name
	 * @param  int $id
	 *
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function deleteUser($application_name, $id)
	{
		try {
			$user = Auth::user();
			$application = $user->applications()->where('name', $application_name)->first();

			// Send error if application does not exist
			if (!$application) {
				return $this->returnApplicationNameError();
			}

			$applicationUser = ApplicationUser::where([
				'user_id' => $id,
				'application_id' => $application->id
			])->first();

			// Send error if user does not exist in the team
			if (!$applicationUser) {
				return $this->returnError('user', 404, 'delete');
			}

			// Check whether user has permission to delete
			if (!$this->hasPermission($user, $application)) {
				return $this->returnError('application', 403, 'delete user');
			}

			if ($applicationUser->delete()) {
				return $this->returnSuccessMessage('message', 'User has been removed from application successfully.');
			}

			// Send error if there is an error on delete
			return $this->returnError('user', 503, 'delete');
		} catch (Exception $e) {
			// Send error
			return $this->returnErrorMessage(503, $e->getMessage());
		}
	}

	/**
	 * Check whether user has permission or not
	 *
	 * @param  $user
	 * @param  $application
	 *
	 * @return bool
	 */
	protected function hasPermission($user, $application)
	{
		$role = ApplicationUser::where([
			'user_id' => $user->id,
			'application_id' => $application->id
		])->first()->role;

		if ($user->role->name != 'Super Admin' && $role->name != 'Admin') {
			return false;
		}

		return true;
	}
}
