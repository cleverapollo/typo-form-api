<?php

namespace App\Http\Controllers;

use Auth;
use App\User;
use Exception;
use App\Models\Team;
use App\Models\TeamUser;
use App\Models\TeamInvitation;
use App\Http\Resources\TeamResource;
use App\Http\Resources\UserResource;
use App\Http\Resources\TeamUserResource;
use Illuminate\Http\Request;

class TeamController extends Controller
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
	 * @param  int $application_id
	 *
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function index($application_id)
	{
		$teams = Auth::user()->teams()->where([
			'application_id' => $application_id
		])->get();

		return $this->returnSuccessMessage('teams', TeamResource::collection($teams));
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @param  int $application_id
	 * @param  \Illuminate\Http\Request $request
	 *
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function store($application_id, Request $request)
	{
		$this->validate($request, [
			'name' => 'required|max:191',
			'invitations' => 'array'
		]);

		try {
			$share_token = base64_encode(str_random(40));
			while (!is_null(Team::where('share_token', $share_token)->first())) {
				$share_token = base64_encode(str_random(40));
			}

			// Create team
			$team = Auth::user()->teams()->create([
				'name' => $request->input('name'),
				'description' => $request->input('description', null),
				'application_id' => $application_id,
				'share_token' => $share_token
			]);

			if ($team) {
				// Send invitation
				$invitations = $request->input('invitations', []);
				$this->sendInvitation('team', $team, $invitations);

				return $this->returnSuccessMessage('team', new TeamResource($team));
			}

			// Send error if team is not created
			return $this->returnError('team', 503, 'create');
		} catch (Exception $e) {
			// Send error
			return $this->returnErrorMessage($e->getCode(), $e->getMessage());
		}
	}

	/**
	 * Display the specified resource.
	 *
	 * @param  int $application_id
	 * @param  int $id
	 *
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function show($application_id, $id)
	{
		$team = Auth::user()->teams()->where([
			'team_id' => $id,
			'application_id' => $application_id
		])->first();

		if ($team) {
			return $this->returnSuccessMessage('team', new TeamResource($team));
		}

		// Send error if application does not exist
		return $this->returnError('team', 404, 'show');
	}

	/**
	 * Get users for the Team.
	 *
	 * @param  int $application_id
	 * @param  int $id
	 *
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function getUsers($application_id, $id)
	{
		$team = Auth::user()->teams()->where([
			'team_id' => $id,
			'application_id' => $application_id
		])->first();

		if ($team) {
			$currentUsers = $team->users()->get();

			$invitedUsers = TeamInvitation::where([
				'team_id' => $team->id,
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
		return $this->returnError('team', 404, 'show users');
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  int $application_id
	 * @param  int $id
	 * @param  \Illuminate\Http\Request $request
	 *
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function update($application_id, $id, Request $request)
	{
		$this->validate($request, [
			'name' => 'filled|max:191'
		]);

		try {
			$user = Auth::user();
			$team = $user->teams()->where([
				'team_id' => $id,
				'application_id' => $application_id
			])->first();

			// Send error if team does not exist
			if (!$team) {
				return $this->returnError('team', 404, 'update');
			}

			// Check whether user has permission to update
			if (!$this->hasPermission($user, $team)) {
				return $this->returnError('team', 403, 'update');
			}

			// Update team
			if ($team->fill($request->only('name', 'description'))->save()) {
				return $this->returnSuccessMessage('team', new TeamResource($team));
			}

			// Send error if there is an error on update
			return $this->returnError('team', 503, 'update');
		} catch (Exception $e) {
			// Send error
			return $this->returnErrorMessage($e->getCode(), $e->getMessage());
		}
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int $application_id
	 * @param  int $id
	 *
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function destroy($application_id, $id)
	{
		try {
			$user = Auth::user();
			$team = $user->teams()->where([
				'team_id' => $id,
				'application_id' => $application_id
			])->first();

			// Check whether user has permission to delete
			if (!$this->hasPermission($user, $team)) {
				return $this->returnError('team', 403, 'delete');
			}

			if ($team->delete()) {
				return $this->returnSuccessMessage('message', 'Team has been deleted successfully.');
			}

			// Send error if there is an error on update
			return $this->returnError('team', 503, 'delete');
		} catch (Exception $e) {
			// Send error
			return $this->returnErrorMessage($e->getCode(), $e->getMessage());
		}
	}

	/**
	 * Get Team invitation token.
	 *
	 * @param $application_id
	 * @param $id
	 *
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function getInvitationToken($application_id, $id)
	{
		$user = Auth::user();
		$team = $user->teams()->where([
			'team_id' => $id,
			'application_id' => $application_id
		])->first();

		// Send error if team does not exist
		if (!$team) {
			return $this->returnError('team', 404, 'get invitation token');
		}

		// Check whether user has permission to delete
		if (!$this->hasPermission($user, $team)) {
			return $this->returnError('team', 403, 'get invitation token');
		}

		return $this->returnSuccessMessage('shareToken', $team->share_token);
	}

	/**
	 * Invite users to the Team.
	 *
	 * @param $application_id
	 * @param $id
	 * @param  \Illuminate\Http\Request $request
	 *
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function inviteUsers($application_id, $id, Request $request)
	{
		$user = Auth::user();
		$team = $user->teams()->where([
			'team_id' => $id,
			'application_id' => $application_id
		])->first();

		// Send error if team does not exist
		if (!$team) {
			return $this->returnError('team', 404, 'send invitation');
		}

		// Check whether user has permission to send invitation
		if (!$this->hasPermission($user, $team)) {
			return $this->returnError('team', 403, 'send invitation');
		}

		// Send invitation
		$invitations = $request->input('invitations', []);
		$this->sendInvitation('team', $team, $invitations);

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
		return $this->acceptInvitation('team', $token);
	}

	/**
	 * Join to the Team.
	 *
	 * @param $token
	 *
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function join($token)
	{
		return $this->acceptJoin('team', $token);
	}

	/**
	 * Update user role in the Team.
	 *
	 * @param $application_id
	 * @param $team_id
	 * @param $id
	 * @param Request $request
	 *
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function updateUser($application_id, $team_id, $id, Request $request)
	{
		$this->validate($request, [
			'role' => 'required|max:191'
		]);

		try {
			$user = Auth::user();
			$team = $user->teams()->where([
				'team_id' => $team_id,
				'application_id' => $application_id
			])->first();

			// Send error if team does not exist
			if (!$team) {
				return $this->returnError('team', 404, 'update user');
			}

			$teamUser = TeamUser::where([
				'user_id' => $id,
				'team_id' => $team->id
			])->first();

			// Send error if user does not exist in the team
			if (!$teamUser) {
				return $this->returnError('user', 404, 'update role');
			}

			// Check whether user has permission to delete
			if (!$this->hasPermission($user, $team)) {
				return $this->returnError('team', 403, 'update user');
			}

			// Update user role
			if ($teamUser->fill($request->only('role'))->save()) {
				return $this->returnSuccessMessage('user', new TeamUserResource($teamUser));
			}

			// Send error if there is an error on update
			return $this->returnError('user role', 503, 'update');
		} catch (Exception $e) {
			// Send error
			return $this->returnErrorMessage($e->getCode(), $e->getMessage());
		}
	}

	/**
	 * Delete user from the Team.
	 *
	 * @param $application_id
	 * @param $team_id
	 * @param $id
	 *
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function deleteUser($application_id, $team_id, $id)
	{
		try {
			$user = Auth::user();
			$team = $user->teams()->where([
				'team_id' => $team_id,
				'application_id' => $application_id
			])->first();

			// Send error if team does not exist
			if (!$team) {
				return $this->returnError('team', 404, 'delete user');
			}

			$teamUser = TeamUser::where([
				'user_id' => $id,
				'team_id' => $team->id
			])->first();

			// Send error if user does not exist in the team
			if (!$teamUser) {
				return $this->returnError('user', 404, 'delete');
			}

			// Check whether user has permission to delete
			if (!$this->hasPermission($user, $team)) {
				return $this->returnError('team', 403, 'delete user');
			}

			if ($teamUser->delete()) {
				return $this->returnSuccessMessage('message', 'User has been removed from team successfully.');
			}

			// Send error if there is an error on delete
			return $this->returnError('user', 503, 'delete');
		} catch (Exception $e) {
			// Send error
			return $this->returnErrorMessage($e->getCode(), $e->getMessage());
		}
	}

	/**
	 * Check whether user has permission or not
	 *
	 * @param $user
	 * @param $team
	 *
	 * @return bool
	 */
	protected function hasPermission($user, $team)
	{
		$role = TeamUser::where([
			'user_id' => $user->id,
			'team_id' => $team->id
		])->value('role');

		if ($user->role != "SuperAdmin" && $role != "Admin") {
			return false;
		}

		return true;
	}
}
