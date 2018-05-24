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
use App\Notifications\InformedNotification;
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
		$this->middleware('auth:api', ['except' => 'show']);
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
	 * @throws \Illuminate\Validation\ValidationException
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

			$name = $request->input('name');
			$slug = strtolower(str_replace(' ', '', $name));
			if ($user->applications()->where('slug', $slug)->count() > 0) {
				return response()->json([
					'slug' => ['The slug has already been taken.']
				], 422);
			}

			// Create application
			$application = $user->applications()->create([
				'name' => $name,
				'slug' => $slug,
				'css' => $request->input('css', null),
				'icon' => $request->input('icon', null),
				'share_token' => $share_token
			], [
				'role_id' => Role::where('name', 'Admin')->first()->id
			]);

			if ($application) {
				// Send invitation
				$invitations = $request->input('invitations', []);
				$this->sendInvitation('application', $application, $invitations);

				// Send notification email to application admin and super admin
				if ($user->email) {
					$user->notify(new InformedNotification('New application has been created successfully.'));
				}
				$super_admins = $this->getSuperAdmins();
				foreach ($super_admins as $super_admin) {
					if ($super_admin->email) {
						$super_admin->notify(new InformedNotification('New application has been created successfully.'));
					}
				}

				$this->createApplicationEmail($application, $user->email);

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
	 * Store a newly created email resource in storage.
	 *
	 * @param  $application
	 * @param  $email
	 *
	 * @return \Exception
	 */
	public function createApplicationEmail($application, $email)
	{
		try {
			// Create application email
			$application_email = $application->emails()->create([
				'recipients' => $email,
				'subject' => 'Create submission',
				'body' => 'Submission is created successfully. Please fill out the form and send submission.',
			]);

			if (!$application_email) {
				throw new Exception('Cannot create application email.');
			}
		} catch (Exception $e) {
			// Send error
			return $e;
		}
	}

	/**
	 * Display the specified resource.
	 *
	 * @param  string $application_slug
	 *
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function show($application_slug)
	{
		$user = Auth::user();
		$application = Application::where('slug', $application_slug)->first();

		if ($user) {
			$application = $user->applications()->where('slug', $application_slug)->first();
		}

		if ($application) {
			return $this->returnSuccessMessage('application', new ApplicationResource($application));
		}

		// Send error if application does not exist
		return $this->returnError('application', 404, 'show');
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  string $application_slug
	 * @param  \Illuminate\Http\Request $request
	 *
	 * @return \Illuminate\Http\JsonResponse
	 * @throws \Illuminate\Validation\ValidationException
	 */
	public function update($application_slug, Request $request)
	{
		$this->validate($request, [
			'name' => 'filled|unique:applications|max:191'
		]);

		try {
			$user = Auth::user();
			$application = $user->applications()->where('slug', $application_slug)->first();

			// Send error if application does not exist
			if (!$application) {
				return $this->returnError('application', 404, 'update');
			}

			// Check whether user has permission to update
			if (!$this->hasPermission($user, $application)) {
				return $this->returnError('application', 403, 'update');
			}

			// Update application
			$name = $request->input('name');
			if ($name) {
				$slug = strtolower(str_replace(' ', '', $name));
				if ($user->applications()->where('slug', $slug)->count() > 0) {
					return response()->json([
						'slug' => ['The slug has already been taken.']
					], 422);
				}
				$application->slug = $slug;
			}

			if ($application->fill($request->only('name', 'css', 'icon'))->save()) {
				// Send notification email to application admin and super admin
				$admin_users = $this->applicationAdmins($application->id);
				foreach ($admin_users as $admin_user) {
					if ($admin_user->email) {
						$admin_user->notify(new InformedNotification('Application has been updated successfully.'));
					}
				}
				$super_admins = $this->getSuperAdmins();
				foreach ($super_admins as $super_admin) {
					if ($super_admin->email) {
						$super_admin->notify(new InformedNotification('Application has been updated successfully.'));
					}
				}

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
	 * @param  string $application_slug
	 *
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function destroy($application_slug)
	{
		try {
			$user = Auth::user();
			$application = $user->applications()->where('slug', $application_slug)->first();

			// Check whether user has permission to delete
			if (!$this->hasPermission($user, $application)) {
				return $this->returnError('application', 403, 'delete');
			}

			$admin_users = $this->applicationAdmins($application->id);
			// Delete Application
			if ($application->delete()) {
				// Send notification email to application admin and super admin
				foreach ($admin_users as $admin_user) {
					if ($admin_user->email) {
						$admin_user->notify(new InformedNotification('Application has been deleted successfully.'));
					}
				}
				$super_admins = $this->getSuperAdmins();
				foreach ($super_admins as $super_admin) {
					if ($super_admin->email) {
						$super_admin->notify(new InformedNotification('Application has been deleted successfully.'));
					}
				}

				return $this->returnSuccessMessage('message', 'Application has been deleted successfully.');
			}

			// Send error if there is an error on delete
			return $this->returnError('application', 503, 'delete');
		} catch (Exception $e) {
			// Send error
			return $this->returnErrorMessage(503, $e->getMessage());
		}
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
	 * @param  string $application_slug
	 *
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function getUsers($application_slug)
	{
		$user = Auth::user();
		$application = $user->applications()->where('slug', $application_slug)->first();

		// Send error if application does not exist
		if (!$application) {
			return $this->returnApplicationNameError();
		}

		// Check whether user has permission to get
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
	 * @param  string $application_slug
	 * @param  \Illuminate\Http\Request $request
	 *
	 * @return \Illuminate\Http\JsonResponse
	 * @throws \Illuminate\Validation\ValidationException
	 */
	public function inviteUsers($application_slug, Request $request)
	{
		$this->validate($request, [
			'invitations' => 'array',
			'invitations.*.email' => 'required|email',
			'invitations.*.application_role_id' => 'required|integer|min:2'
		]);

		$user = Auth::user();
		$application = $user->applications()->where('slug', $application_slug)->first();

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
	 * @param  string $application_slug
	 * @param  int $id
	 * @param  Request $request
	 *
	 * @return \Illuminate\Http\JsonResponse
	 * @throws \Illuminate\Validation\ValidationException
	 */
	public function updateUser($application_slug, $id, Request $request)
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
			$application = $user->applications()->where('slug', $application_slug)->first();

			// Send error if application does not exist
			if (!$application) {
				return $this->returnApplicationNameError();
			}

			$application_user = ApplicationUser::where([
				'user_id' => $id,
				'application_id' => $application->id
			])->first();

			// Send error if user does not exist in the team
			if (!$application_user) {
				return $this->returnError('user', 404, 'update role');
			}

			// Check whether user has permission to update
			if (!$this->hasPermission($user, $application)) {
				return $this->returnError('application', 403, 'update user');
			}

			// Update user role
			if ($application_user->fill(['role_id' => $role->id])->save()) {
				// Send notification email to application user
				if ($application_user->email) {
					$application_user->notify(new InformedNotification('Application user role has been updated successfully.'));
				}

				return $this->returnSuccessMessage('user', new ApplicationUserResource($application_user));
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
	 * @param  string $application_slug
	 * @param  int $id
	 *
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function deleteUser($application_slug, $id)
	{
		try {
			$user = Auth::user();
			$application = $user->applications()->where('slug', $application_slug)->first();

			// Send error if application does not exist
			if (!$application) {
				return $this->returnApplicationNameError();
			}

			$application_user = ApplicationUser::where([
				'user_id' => $id,
				'application_id' => $application->id
			])->first();

			// Send error if user does not exist in the team
			if (!$application_user) {
				return $this->returnError('user', 404, 'delete');
			}

			// Check whether user has permission to delete
			if (!$this->hasPermission($user, $application)) {
				return $this->returnError('application', 403, 'delete user');
			}

			if ($application_user->delete()) {
				// Send notification email to application admin and super admin
				$admin_users = $this->applicationAdmins($application->id);
				foreach ($admin_users as $admin_user) {
					if ($admin_user->email) {
						$admin_user->notify(new InformedNotification('User has been deleted from application successfully.'));
					}
				}
				if ($application_user->email) {
					$application_user->notify(new InformedNotification('You have been deleted from application.'));
				}

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
	 * Get Super Admin list
	 *
	 * @return mixed
	 */
	protected function getSuperAdmins()
	{
		return User::where('role_id', Role::where('name', 'Super Admin')->first()->id)->get();
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
