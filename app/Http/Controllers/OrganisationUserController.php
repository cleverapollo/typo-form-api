<?php

namespace App\Http\Controllers;

use App\Http\Resources\OrganisationUserResource;
use App\Jobs\UsersNotification;
use App\Models\OrganisationUser;
use App\Repositories\ApplicationUserRepository;
use App\User;
use Auth;
use Illuminate\Http\Request;

class OrganisationUserController extends Controller
{
    private $applicationService;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(ApplicationUserRepository $applicationUserRepository)
    {
        $this->middleware('auth:api');
        $this->middleware('resolve-application-slug');
        $this->middleware('application-admin');
        $this->applicationUserRepository = $applicationUserRepository;
    }

    /**
     * Get users for the Organisation.
     *
     * @param  string $application_slug
     * @param  int $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request, $application_slug, $id)
    {
        $user = Auth::user();
        $application = $request->get('application');

        $organisation = $application->organisations()->where('id', $id)->firstOrFail();
        $users = OrganisationUser::whereOrganisationId($organisation->id)->get();

        return OrganisationUserResource::collection($users);
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
    public function update(Request $request, $application_slug, $id, $user_id)
    {
        $this->validate($request, [
            'organisation_role_id' => 'required|integer|min:2'
        ]);

        $role = Role::findOrFail($request->input('organisation_role_id'));
        $user = Auth::user();
        $application = $request->get('application');

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
}