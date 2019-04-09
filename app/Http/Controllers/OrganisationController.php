<?php

namespace App\Http\Controllers;

use App\Http\Resources\InvitationResource;
use Auth;
use Exception;
use App\Models\Role;
use App\Models\Type;
use App\Models\Application;
use App\Models\Organisation;
use App\Models\OrganisationUser;
use App\Models\Invitation;
use App\Http\Resources\OrganisationResource;
use App\Http\Resources\UserResource;
use App\Http\Resources\OrganisationUserResource;
use Illuminate\Http\Request;

class OrganisationController extends Controller
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
	 * @param  string $application_slug
	 *
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function index($application_slug)
	{
		// Get Application
		$user = Auth::user();
		if(!$application = $this->getApplication($user, $application_slug)) {
			return $this->returnApplicationNameError();
		}

		// Get Organisations
		$organisations = $this->isUserApplicationAdmin($user, $application) ? Organisation::where('application_id', $application->id)->get() : $user->organisations()->get();

		return $this->returnSuccessMessage('organisations', OrganisationResource::collection($organisations));
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @param  string $application_slug
	 * @param  \Illuminate\Http\Request $request
	 *
	 * @return \Illuminate\Http\JsonResponse
	 * @throws \Illuminate\Validation\ValidationException
	 */
	public function store($application_slug, Request $request)
	{
		$this->validate($request, [
			'name' => 'required|max:191'
		]);

		try {
            $user = Auth::user();
            if ($user->role->name == 'Super Admin') {
                $application = Application::where('slug', $application_slug)->first();
            } else {
                $application = $user->applications()->where('slug', $application_slug)->first();
            }

			// Send error if application does not exist
			if (!$application) {
				return $this->returnApplicationNameError();
			}

			$share_token = base64_encode(str_random(40));
			while (!is_null(Organisation::where('share_token', $share_token)->first())) {
				$share_token = base64_encode(str_random(40));
			}

			// Create organisation
			$organisation = $user->organisations()->create([
				'name' => $request->input('name'),
                'description' => $request->input('description', null),
				'application_id' => $application->id,
				'share_token' => $share_token
			], [
				'role_id' => Role::where('name', 'Admin')->first()->id
			]);

			if ($organisation) {
				return $this->returnSuccessMessage('organisation', new OrganisationResource($organisation));
			}

			// Send error if organisation is not created
			return $this->returnError('organisation', 503, 'create');
		} catch (Exception $e) {
			// Send error
			return $this->returnErrorMessage(503, $e->getMessage());
		}
	}

	/**
	 * Display the specified resource.
	 *
	 * @param  string $application_slug
	 * @param  int $id
	 *
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function show($application_slug, $id)
	{
        $user = Auth::user();
        if ($user->role->name == 'Super Admin') {
            $application = Application::where('slug', $application_slug)->first();
        } else {
            $application = $user->applications()->where('slug', $application_slug)->first();
        }

		// Send error if application does not exist
		if (!$application) {
			return $this->returnApplicationNameError();
		}

		$organisation = $user->organisations()->where([
			'organisation_id' => $id,
			'application_id' => $application->id
		])->first();

		if ($user->role->name == 'Super Admin') {
			$organisation = Organisation::with(['forms', 'users'])->where([
				'id' => $id,
				'application_id' => $application->id
			])->first();
		}

		if ($organisation) {
			return $this->returnSuccessMessage('organisation', new OrganisationResource($organisation));
		}

		// Send error if application does not exist
		return $this->returnError('organisation', 404, 'show');
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  string $application_slug
	 * @param  int $id
	 * @param  \Illuminate\Http\Request $request
	 *
	 * @return \Illuminate\Http\JsonResponse
	 * @throws \Illuminate\Validation\ValidationException
	 */
	public function update($application_slug, $id, Request $request)
	{
		$this->validate($request, [
			'name' => 'filled|max:191'
		]);

		try {
            $user = Auth::user();
            if ($user->role->name == 'Super Admin') {
                $application = Application::where('slug', $application_slug)->first();
            } else {
                $application = $user->applications()->where('slug', $application_slug)->first();
            }

			// Send error if application does not exist
			if (!$application) {
				return $this->returnApplicationNameError();
			}

			$organisation = $user->organisations()->where([
				'organisation_id' => $id,
				'application_id' => $application->id
			])->first();

			if ($user->role->name == 'Super Admin') {
				$organisation = Organisation::with(['forms', 'users'])->where([
					'id' => $id,
					'application_id' => $application->id
				])->first();
			}

			// Send error if organisation does not exist
			if (!$organisation) {
				return $this->returnError('organisation', 404, 'update');
			}

			// Check whether user has permission to update
			if (!$this->hasPermission($user, $organisation)) {
				return $this->returnError('organisation', 403, 'update');
			}

			// Update organisation
			if ($organisation->fill($request->only('name', 'description'))->save()) {
				return $this->returnSuccessMessage('organisation', new OrganisationResource($organisation));
			}

			// Send error if there is an error on update
			return $this->returnError('organisation', 503, 'update');
		} catch (Exception $e) {
			// Send error
			return $this->returnErrorMessage(503, $e->getMessage());
		}
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  string $application_slug
	 * @param  int $id
	 *
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function destroy($application_slug, $id)
	{
		try {
            $user = Auth::user();
            if ($user->role->name == 'Super Admin') {
                $application = Application::where('slug', $application_slug)->first();
            } else {
                $application = $user->applications()->where('slug', $application_slug)->first();
            }

			// Send error if application does not exist
			if (!$application) {
				return $this->returnApplicationNameError();
			}

			$organisation = $user->organisations()->where([
				'organisation_id' => $id,
				'application_id' => $application->id
			])->first();

			if ($user->role->name == 'Super Admin') {
				$organisation = Organisation::where([
					'id' => $id,
					'application_id' => $application->id
				])->first();
			}

			// Check whether user has permission to delete
			if (!$this->hasPermission($user, $organisation)) {
				return $this->returnError('organisation', 403, 'delete');
			}

			if ($organisation->delete()) {
				return $this->returnSuccessMessage('message', 'Organisation has been deleted successfully.');
			}

			// Send error if there is an error on delete
			return $this->returnError('organisation', 503, 'delete');
		} catch (Exception $e) {
			// Send error
			return $this->returnErrorMessage(503, $e->getMessage());
		}
	}

	/**
	 * Get Organisation invitation token.
	 *
	 * @param  string $application_slug
	 * @param  int $id
	 *
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function getInvitationToken($application_slug, $id)
	{
        $user = Auth::user();
        if ($user->role->name == 'Super Admin') {
            $application = Application::where('slug', $application_slug)->first();
        } else {
            $application = $user->applications()->where('slug', $application_slug)->first();
        }

		// Send error if application does not exist
		if (!$application) {
			return $this->returnApplicationNameError();
		}

		$organisation = $user->organisations()->where([
			'organisation_id' => $id,
			'application_id' => $application->id
		])->first();

		if ($user->role->name == 'Super Admin') {
			$organisation = Organisation::where([
				'id' => $id,
				'application_id' => $application->id
			])->first();
		}

		// Send error if organisation does not exist
		if (!$organisation) {
			return $this->returnError('organisation', 404, 'get invitation token');
		}

		// Check whether user has permission to delete
		if (!$this->hasPermission($user, $organisation)) {
			return $this->returnError('organisation', 403, 'get invitation token');
		}

		return $this->returnSuccessMessage('shareToken', $organisation->share_token);
	}

	/**
	 * Join to the Organisation.
	 *
	 * @param  string $token
	 *
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function join($token)
	{
		return $this->acceptJoin('organisation', $token);
	}

    /**
     * Get users for the Organisation.
     *
     * @param  string $application_slug
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUsers($application_slug)
    {
        $user = Auth::user();
        if ($user->role->name == 'Super Admin') {
            $application = Application::where('slug', $application_slug)->first();
        } else {
            $application = $user->applications()->where('slug', $application_slug)->first();
        }

        // Send error if application does not exist
        if (!$application) {
            return $this->returnApplicationNameError();
        }

        $organisations = $user->organisations()->where([
            'application_id' => $application->id
        ])->get();

        if ($user->role->name == 'Super Admin') {
            $organisations = Organisation::where([
                'application_id' => $application->id
            ])->get();
        }

        $users = [];
        foreach ($organisations as $organisation) {
            if ($organisation) {
                $currentUsers = $organisation->users()->get();
                $type = Type::where('name', 'organisation')->first();

                $invitedUsers = Invitation::where([
                    'reference_id' => $organisation->id,
                    'status' => 0,
                    'type_id' => $type->id
                ])->get();

                $users[] = [
                    'current' => UserResource::collection($currentUsers),
                    'unaccepted' => InvitationResource::collection($invitedUsers),
                    'organisation_id' => $organisation->id
                ];
            }
        }
        return $this->returnSuccessMessage('users', $users);
    }

	/**
	 * Get users for the Organisation.
	 *
	 * @param  string $application_slug
	 * @param  int $id
	 *
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function getOrganisationUsers($application_slug, $id)
	{
		// Get Application
		$user = Auth::user();
		if(!$application = $this->getApplication($user, $application_slug)) {
			return $this->returnApplicationNameError();
		}

		// Get Organisation
		if(!$organisation = $application->organisations()->where('application_id', $application->id)->where('id', $id)->first()) {
			return $this->returnError('organisation', 404, 'show organisation');
		}

		$users = $organisation->users()->get();
		$type = Type::where('name', 'organisation')->first();

		$invitedUsers = Invitation::where([
			'reference_id' => $organisation->id,
			'status' => 0,
			'type_id' => $type->id
		])->get();

		return $this->returnSuccessMessage('users', [
			'current' => UserResource::collection($users),
			'unaccepted' => InvitationResource::collection($invitedUsers)
		]);

		// Send error if application does not exist
		return $this->returnError('organisation', 404, 'show users');
	}

	/**
	 * Invite users to the Organisation.
	 *
	 * @param  string $application_slug
	 * @param  int $id
	 * @param  \Illuminate\Http\Request $request
	 *
	 * @return \Illuminate\Http\JsonResponse
	 * @throws \Illuminate\Validation\ValidationException
	 */
	public function inviteUsers($application_slug, $id, Request $request)
	{
		$this->validate($request, [
			'invitations' => 'array',
			'invitations.*.email' => 'required|email',
			'role_id' => 'required|integer|min:2'
		]);

		// Get Application
		$user = Auth::user();
		if(!$application = $this->getApplication($user, $application_slug)) {
			return $this->returnApplicationNameError();
		}

		// Get Organisation
		if(!$organisation = $application->organisations()->where('application_id', $application->id)->where('id', $id)->first()) {
			return $this->returnError('organisation', 404, 'send invitation');
		}

		$invitations = $request->input('invitations', []);
		$host = $request->header('Origin');
        $role_id = $request->input('role_id');

		// Send invitation
		$this->sendInvitation('organisation', $organisation, $invitations, $host, $role_id);

		return $this->returnSuccessMessage('message', 'Invitation has been sent successfully.');
	}

	/**
	 * Update user role in the Organisation.
	 *
	 * @param  string $application_slug
	 * @param  int $id
	 * @param  int $user_id
	 * @param  Request $request
	 *
	 * @return \Illuminate\Http\JsonResponse
	 * @throws \Illuminate\Validation\ValidationException
	 */
	public function updateUser($application_slug, $id, $user_id, Request $request)
	{
		$this->validate($request, [
			'organisation_role_id' => 'required|integer|min:2'
		]);

		try {
			// Check whether the role exists or not
			$role = Role::find($request->input('organisation_role_id'));
			if (!$role) {
				return $this->returnError('role', 404, 'update user');
			}

            $user = Auth::user();
            if ($user->role->name == 'Super Admin') {
                $application = Application::where('slug', $application_slug)->first();
            } else {
                $application = $user->applications()->where('slug', $application_slug)->first();
            }

			// Send error if application does not exist
			if (!$application) {
				return $this->returnApplicationNameError();
			}

			$organisation = $user->organisations()->where([
				'organisation_id' => $id,
				'application_id' => $application->id
			])->first();

			if ($user->role->name == 'Super Admin') {
				$organisation = Organisation::where([
					'id' => $id,
					'application_id' => $application->id
				])->first();
			}

			// Send error if organisation does not exist
			if (!$organisation) {
				return $this->returnError('organisation', 404, 'update user');
			}

			$organisation_user = OrganisationUser::where([
				'user_id' => $user_id,
				'organisation_id' => $organisation->id
			])->first();

			// Send error if user does not exist in the organisation
			if (!$organisation_user) {
				return $this->returnError('user', 404, 'update role');
			}

			// Check whether user has permission to delete
			if (!$this->hasPermission($user, $organisation)) {
				return $this->returnError('organisation', 403, 'update user');
			}

			// Update user role
			if ($organisation_user->fill(['role_id' => $role->id])->save()) {
				return $this->returnSuccessMessage('user', new OrganisationUserResource($organisation_user));
			}

			// Send error if there is an error on update
			return $this->returnError('user role', 503, 'update');
		} catch (Exception $e) {
			// Send error
			return $this->returnErrorMessage(503, $e->getMessage());
		}
	}

	/**
	 * Delete user from the Organisation.
	 *
	 * @param  string $application_slug
	 * @param  int $id
	 * @param  int $user_id
	 *
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function deleteUser($application_slug, $id, $user_id)
	{
		try {
            $user = Auth::user();
            if ($user->role->name == 'Super Admin') {
                $application = Application::where('slug', $application_slug)->first();
            } else {
                $application = $user->applications()->where('slug', $application_slug)->first();
            }

			// Send error if application does not exist
			if (!$application) {
				return $this->returnApplicationNameError();
			}

			if ($user->role->name == 'Super Admin') {
				$organisation = Organisation::where([
					'id' => $id,
					'application_id' => $application->id
				])->first();
			} else {
                $organisation = $user->organisations()->where([
                    'organisation_id' => $id,
                    'application_id' => $application->id
                ])->first();
            }

			// Send error if organisation does not exist
			if (!$organisation) {
				return $this->returnError('organisation', 404, 'delete user');
			}

			$organisation_user = OrganisationUser::where([
				'user_id' => $user_id,
				'organisation_id' => $organisation->id
			])->first();

			// Send error if user does not exist in the organisation
			if (!$organisation_user) {
				return $this->returnError('user', 404, 'delete');
			}

			// Check whether user has permission to delete
			if (!$this->hasPermission($user, $organisation)) {
				return $this->returnError('organisation', 403, 'delete user');
			}

			if ($organisation_user->delete()) {
				return $this->returnSuccessMessage('message', 'User has been removed from organisation successfully.');
			}

			// Send error if there is an error on delete
			return $this->returnError('user', 503, 'delete');
		} catch (Exception $e) {
			// Send error
			return $this->returnErrorMessage(503, $e->getMessage());
		}
	}

	/**
	 * Update user role in the Organisation.
	 *
	 * @param  string $application_slug
	 * @param  int $id
	 * @param  int $invited_id
	 * @param  Request $request
	 *
	 * @return \Illuminate\Http\JsonResponse
	 * @throws \Illuminate\Validation\ValidationException
	 */
	public function updateInvitedUser($application_slug, $id, $invited_id, Request $request)
	{
		$this->validate($request, [
			'organisation_role_id' => 'required|integer|min:2'
		]);

		try {
			// Check whether the role exists or not
			$role = Role::find($request->input('organisation_role_id'));
			if (!$role) {
				return $this->returnError('role', 404, 'update user');
			}

            $user = Auth::user();
            if ($user->role->name == 'Super Admin') {
                $application = Application::where('slug', $application_slug)->first();
            } else {
                $application = $user->applications()->where('slug', $application_slug)->first();
            }

			// Send error if application does not exist
			if (!$application) {
				return $this->returnApplicationNameError();
			}

			$organisation = $user->organisations()->where([
				'organisation_id' => $id,
				'application_id' => $application->id
			])->first();

			if ($user->role->name == 'Super Admin') {
				$organisation = Organisation::where([
					'id' => $id,
					'application_id' => $application->id
				])->first();
			}

			// Send error if organisation does not exist
			if (!$organisation) {
				return $this->returnError('organisation', 404, 'update user');
			}

			$type = Type::where('name', 'organisation')->first();
			$organisation_invitation = Invitation::where([
				'id' => $invited_id,
				'reference_id' => $organisation->id,
                'type_id' => $type->id
			])->first();

			// Send error if invited user does not exist in the organisation
			if (!$organisation_invitation) {
				return $this->returnError('user', 404, 'update role');
			}

			// Check whether user has permission to delete
			if (!$this->hasPermission($user, $organisation)) {
				return $this->returnError('organisation', 403, 'update invited user');
			}

			// Update invited user role
			if ($organisation_invitation->fill(['role_id' => $role->id])->save()) {
				return $this->returnSuccessMessage('user', new InvitationResource($organisation_invitation));
			}

			// Send error if there is an error on update
			return $this->returnError('user role', 503, 'update');
		} catch (Exception $e) {
			// Send error
			return $this->returnErrorMessage(503, $e->getMessage());
		}
	}

	/**
	 * Delete user from the Organisation.
	 *
	 * @param  string $application_slug
	 * @param  int $id
	 * @param  int $invited_id
	 *
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function deleteInvitedUser($application_slug, $id, $invited_id)
	{
		try {
            $user = Auth::user();
            if ($user->role->name == 'Super Admin') {
                $application = Application::where('slug', $application_slug)->first();
            } else {
                $application = $user->applications()->where('slug', $application_slug)->first();
            }

			// Send error if application does not exist
			if (!$application) {
				return $this->returnApplicationNameError();
			}

			$organisation = $user->organisations()->where([
				'organisation_id' => $id,
				'application_id' => $application->id
			])->first();


			if ($user->role->name == 'Super Admin') {
				$organisation = Organisation::where([
					'id' => $id,
					'application_id' => $application->id
				])->first();
			}

			// Send error if organisation does not exist
			if (!$organisation) {
				return $this->returnError('organisation', 404, 'delete user');
			}

            $type = Type::where('name', 'organisation')->first();
            $organisation_invitation = Invitation::where([
                'id' => $invited_id,
                'reference_id' => $organisation->id,
                'type_id' => $type->id
            ])->first();

			// Send error if invited user does not exist in the organisation
			if (!$organisation_invitation) {
				return $this->returnError('user', 404, 'delete');
			}

			// Check whether user has permission to delete
			if (!$this->hasPermission($user, $organisation)) {
				return $this->returnError('organisation', 403, 'delete invited user');
			}

			if ($organisation_invitation->delete()) {
				return $this->returnSuccessMessage('message', 'Invited User has been removed from organisation successfully.');
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
	 * @param  $organisation
	 *
	 * @return bool
	 */
	protected function hasPermission($user, $organisation)
	{
		if ($user->role->name == 'Super Admin') {
			return true;
		}

		$role = OrganisationUser::where([
			'user_id' => $user->id,
			'organisation_id' => $organisation->id
		])->first()->role;

		if ($role->name != 'Admin') {
			return false;
		}

		return true;
	}
}
