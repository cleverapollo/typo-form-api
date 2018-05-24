<?php

namespace App\Http\Controllers;

use Auth;
use Exception;
use Carbon\Carbon;
use App\User;
use App\Models\Application;
use App\Models\ApplicationUser;
use App\Models\TeamUser;
use App\Models\Action;
use App\Models\Role;
use App\Models\Status;
use App\Models\ActionType;
use App\Http\Foundation\Auth\Access\AuthorizesRequests;
use App\Notifications\InformedNotification;
use Laravel\Lumen\Routing\Controller as BaseController;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\Request;

class Controller extends BaseController
{
	use AuthorizesRequests;

	/**
	 * Return error response
	 *
	 * @param $data
	 * @param $status
	 * @param $action
	 *
	 * @return \Illuminate\Http\JsonResponse
	 */
	protected function returnError($data, $status, $action)
	{
		$errorMsg = '';
		if ($status == 404) {
			$errorMsg = 'There is no ' . $data . ' with this ID.';
		} else if ($status == 503) {
			$errorMsg = 'Failed to ' . $action . ' ' . $data . '. Please try again later.';
		} else if ($status == 403) {
			$errorMsg = 'You do not have permission to ' . $action . '.';
		}

		return $this->returnErrorMessage($status, $errorMsg);
	}

	/**
	 * Return error response
	 *
	 * @return \Illuminate\Http\JsonResponse
	 */
	protected function returnApplicationNameError()
	{
		return $this->returnErrorMessage(404, 'There is no application with this name');
	}

	/**
	 * Return error response
	 *
	 * @param $status
	 * @param $errorMsg
	 *
	 * @return \Illuminate\Http\JsonResponse
	 */
	protected function returnErrorMessage($status, $errorMsg)
	{
		return response()->json([
			'status' => 'fail',
			'message' => $errorMsg
		], $status);
	}

	/**
	 * Return success response
	 *
	 * @param $key
	 * @param $data
	 *
	 * @return \Illuminate\Http\JsonResponse
	 */
	protected function returnSuccessMessage($key, $data)
	{
		return response()->json([
			'status' => 'success',
			$key => $data
		], 200);
	}

	/**
	 * Send invitation.
	 *
	 * @param $type
	 * @param $data
	 * @param $invitations
	 */
	protected function sendInvitation($type, $data, $invitations)
	{
		if ($invitations && count($invitations) > 0) {
			foreach ($invitations as $invitation) {
				// Check whether the role exists or not
				$role = Role::find($invitation[$type . '_role_id']);
				if (!$role) continue;

				$token = base64_encode(str_random(40));
				while (DB::table($type . '_invitations')->where('token', $token)->first()) {
					$token = base64_encode(str_random(40));
				}

				// Check if user is already included in the Team or Application
				$invitee = User::where('email', $invitation['email'])->first();
				if ($invitee) {
					$isIncluded = DB::table($type . '_users')->where([
						'user_id' => $invitee->id,
						$type . '_id' => $data->id
					])->first();

					if ($isIncluded) continue;
				}

				// Check if user is included in the application for team invitation
				if ($type == 'team') {
					// Ignore unregistered emails for team registration
					if (!$invitee) continue;

					$application_user = ApplicationUser::where([
						'user_id' => $invitee->id,
						'application_id' => $data->application_id
					])->first();

					// Ignore if invitee is not the application member for team registration
					if (!$application_user) continue;
				}

				// Check if the user is already invited
				$previousInvitation = DB::table($type . '_invitations')->where([
					'invitee' => $invitation['email'],
					$type . '_id' => $data->id,
					'status' => 0
				])->first();

				if (!$previousInvitation) {
					$user = Auth::user();

					// Input to the invitations table
					DB::table($type . '_invitations')->insert([
						'inviter_id' => $user->id,
						'invitee' => $invitation['email'],
						$type . '_id' => $data->id,
						'role_id' => $role->id,
						'token' => $token,
						'created_at' => Carbon::now(),
						'updated_at' => Carbon::now()
					]);

					// Send email to the invitee
					Mail::send('emails.invitation', [
						'type' => $type,
						'name' => $data->name,
						'userName' => $user->first_name . ' ' . $user->last_name,
						'role' => $role->name,
						'token' => $token
					], function ($message) use ($invitation) {
						$message->from('info@informed365.com', 'Informed 365');
						$message->to($invitation['email']);
					});
				}
			}
		}
	}

	/**
	 * Accept invitation request.
	 *
	 * @param $type
	 * @param $token
	 *
	 * @return \Illuminate\Http\JsonResponse
	 */
	protected function acceptInvitation($type, $token)
	{
		$user = Auth::user();

		$invitation = DB::table($type . '_invitations')->where([
			'invitee' => $user->email,
			'token' => $token,
			'status' => 0
		])->first();

		// Send error if token does not exist
		if (!$invitation) {
			return $this->returnErrorMessage(403, 'Invalid token.');
		}

		if ($type == 'team') {
			$dataId = $invitation->team_id;
		} else {
			$dataId = $invitation->application_id;
		}

		// Send error if user already exists in the Team or Application
		if (DB::table($type . '_users')->where([
			'user_id' => $user->id,
			$type . '_id' => $dataId
		])->first()) {
			return $this->returnErrorMessage(403, 'User is already included in the ' . $type);
		}

		if (DB::table($type . '_users')->insert([
			'user_id' => $user->id,
			$type . '_id' => $dataId,
			'role_id' => $invitation->role_id,
			'created_at' => Carbon::now(),
			'updated_at' => Carbon::now()
		])) {
			// Remove token and update status at invitations table
			DB::table($type . '_invitations')->where('id', $invitation->id)->update([
				'token' => null,
				'status' => 1,
				'updated_at' => Carbon::now()
			]);

			if ($type == 'application') {
				// Send email notification
				if ($user->email) {
					$user->notify(new InformedNotification('Welcome to the application.'));
				}
				$admin_users = $this->applicationAdmins($dataId);
				foreach ($admin_users as $admin_user) {
					if ($admin_user->email) {
						$admin_user->notify(new InformedNotification('User(' . $user->email . ') has accepted invitation to application.'));
					}
				}

				$forms = Application::find($dataId)->forms('auto', true)->get();
				foreach ($forms as $form) {
					$submission = $form->submissions()->create([
						'user_id' => $user->id,
						'team_id' => null,
						'status_id' => Status::where('status', 'opened')->first()->id
					]);

					Action::create([
						'user_id' => $user->id,
						'action_id' => $submission->id,
						'action_type_id' => ActionType::where('type', 'send submission'),
						'trigger_at'
					]);
				}
			} else {
				// Send email notification
				if ($user->email) {
					$user->notify(new InformedNotification('Welcome to the team.'));
				}
				$admin_users = $this->applicationAdmins($dataId);
				foreach ($admin_users as $admin_user) {
					if ($admin_user->email) {
						$admin_user->notify(new InformedNotification('User(' . $user->email . ') has accepted invitation to team.'));
					}
				}
			}

			return $this->returnSuccessMessage('message', 'Invitation has been successfully accepted.');
		};

		// Send error
		return $this->returnErrorMessage(503, 'You cannot accept the invitation right now. Please try again later.');
	}

	/**
	 * Accept join request.
	 *
	 * @param $type
	 * @param $token
	 *
	 * @return \Illuminate\Http\JsonResponse
	 */
	protected function acceptJoin($type, $token)
	{
		$user = Auth::user();

		$data = DB::table($type . 's')->where([
			'share_token' => $token,
		])->first();

		// Send error if token does not exist
		if (!$data) {
			return $this->returnErrorMessage(403, 'Invalid token.');
		}

		// Send error if user already exists in the Team or Application
		if (DB::table($type . '_users')->where([
			'user_id' => $user->id,
			$type . '_id' => $data->id
		])->first()) {
			return $this->returnErrorMessage(403, 'User is already included in the ' . $type);
		}

		if (DB::table($type . '_users')->insert([
			'user_id' => $user->id,
			$type . '_id' => $data->id,
			'role_id' => Role::where('name', 'User')->first()->id,
			'created_at' => Carbon::now(),
			'updated_at' => Carbon::now()
		])) {
			return $this->returnSuccessMessage('message', 'You have joined the ' . $type . ' successfully.');
		};

		// Send error
		return $this->returnErrorMessage(503, 'You cannot join the ' . $type . ' right now. Please try again later.');
	}

	/**
	 * File Upload
	 *
	 * @param  Request $request
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function fileUpload(Request $request)
	{
		try {
			if ($request->hasFile('file')) {
				$file = $request->file('file');
				if ($file->isValid()) {
					$file_name = time() . '_' . $file->getClientOriginalName();
					$file->move(public_path() . '/uploads', $file_name);
					return $this->returnSuccessMessage('path', $file_name);
				}

				return $this->returnErrorMessage(403, 'Invalid file.');
			}

			return $this->returnErrorMessage(404, 'Sorry, we cannot find the file to upload.');
		} catch (Exception $e) {
			// Send error
			return $this->returnErrorMessage(503, $e->getMessage());
		}
	}

	/**
	 * Get application admins
	 *
	 * @param  $application_id
	 *
	 * @return array
	 */
	protected function applicationAdmins($application_id)
	{
		$admins = ApplicationUser::where([
			'application_id' => $application_id,
			'role_id' => Role::where('name', 'Admin')->first()->id
		])->get();

		$admin_users = [];
		foreach ($admins as $admin) {
			$admin_users[] = User::find($admin->user_id);
		}

		return $admin_users;
	}

	/**
	 * Get application admins
	 *
	 * @param  $team_id
	 *
	 * @return array
	 */
	protected function teamAdmins($team_id)
	{
		$admins = TeamUser::where([
			'team_id' => $team_id,
			'role_id' => Role::where('name', 'Admin')->first()->id
		])->get();

		$admin_users = [];
		foreach ($admins as $admin) {
			$admin_users[] = User::find($admin->user_id);
		}

		return $admin_users;
	}
}
