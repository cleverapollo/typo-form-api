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
			'name' => 'required|max:191',
			'invitations' => 'array',
			'invitations.*.email' => 'required|email',
			'invitations.*.application_role_id' => 'required|integer|min:2'
		]);

		$invitations = $request->input('invitations', []);

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

			// Create application
			$application = $user->applications()->create([
				'name' => $request->input('name'),
				'share_token' => $share_token
			], [
				'role_id' => Role::where('name', 'Admin')->first()->id
			]);

			if ($application) {
				// Send invitation
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
		$application = Auth::user()->applications()->where('application_id', $id)->first();
		if ($application) {
			return $this->returnSuccessMessage('application', new ApplicationResource($application));
		}

		// Send error if application does not exist
		return $this->returnError('application', 404, 'show');
	}

	/**
	 * Get users for the Application.
	 *
	 * @param  int $id
	 *
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

		// Send error if application does not exist
		return $this->returnError('application', 404, 'show users');
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
			$application = $user->applications()->where('application_id', $id)->first();

			// Send error if application does not exist
			if (!$application) {
				return $this->returnError('application', 404, 'update');
			}

			// Check whether user has permission to update
			if (!$this->hasPermission($user, $application)) {
				return $this->returnError('application', 403, 'update');
			}

			// Update application
			if ($application->fill($request->only('name'))->save()) {
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
			$application = $user->applications()->where('application_id', $id)->first();

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
	 * @param $id
	 *
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function getInvitationToken($id)
	{
		$user = Auth::user();
		$application = $user->applications()->where('application_id', $id)->first();

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
	 * Invite users to the Application.
	 *
	 * @param $id
	 * @param  \Illuminate\Http\Request $request
	 *
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function inviteUsers($id, Request $request)
	{
		$this->validate($request, [
			'invitations' => 'array',
			'invitations.*.email' => 'required|email',
			'invitations.*.application_role_id' => 'required|integer|min:2'
		]);

		$user = Auth::user();
		$application = $user->applications()->where('application_id', $id)->first();

		// Send error if application does not exist
		if (!$application) {
			return $this->returnError('application', 404, 'send invitation');
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
	 * Accept invitation request.
	 *
	 * @param $token
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
	 * @param $token
	 *
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
			$application = $user->applications()->where('application_id', $application_id)->first();

			// Send error if application does not exist
			if (!$application) {
				return $this->returnError('application', 404, 'update user');
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
	 * @param $application_id
	 * @param $id
	 *
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function deleteUser($application_id, $id)
	{
		try {
			$user = Auth::user();
			$application = $user->applications()->where([
				'application_id' => $application_id
			])->first();

			// Send error if application does not exist
			if (!$application) {
				return $this->returnError('application', 404, 'delete user');
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
		])->first()->role;

		if ($user->role->name != 'Super Admin' && $role->name != 'Admin') {
			return false;
		}

		return true;
	}
}
