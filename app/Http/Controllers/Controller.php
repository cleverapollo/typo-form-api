<?php

namespace App\Http\Controllers;

use Auth;
use Exception;
use Storage;
use Carbon\Carbon;
use App\User;
use App\Models\Application;
use App\Models\Team;
use App\Models\ApplicationUser;
use App\Models\Role;
use App\Http\Foundation\Auth\Access\AuthorizesRequests;
use App\Jobs\ProcessInvitationEmail;
use Laravel\Lumen\Routing\Controller as BaseController;
use Illuminate\Support\Facades\DB;
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
		return $this->returnErrorMessage(404, 'There is no application with this name.');
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

				$inviteeEmail = strtolower($invitation['email']);

				// Check if user is already included in the Team or Application
				$invitee = User::where('email', $inviteeEmail)->first();
				if ($invitee) {
					$isIncluded = DB::table($type . '_users')->where([
						'user_id' => $invitee->id,
						$type . '_id' => $data->id
					])->first();

					if ($isIncluded) continue;
				}

				// Check if user is included in the application for team invitation
//				if ($type == 'team') {
//					// Ignore unregistered emails for team registration
//					if (!$invitee) continue;
//
//					$application_user = ApplicationUser::where([
//						'user_id' => $invitee->id,
//						'application_id' => $data->application_id
//					])->first();
//
//					// Ignore if invitee is not the application member for team registration
//					if (!$application_user) {
//                        $application_user = ApplicationUser::create([
//                            'user_id' => $invitee->id,
//                            'application_id' => $data->application_id,
//                            'role_id' => Role::where('name', 'User')->first()->id
//                        ]);
//                    }
//				}

				// Check if the user is already invited
				$previousInvitation = DB::table($type . '_invitations')->where([
					'invitee' => $inviteeEmail,
					$type . '_id' => $data->id,
					'status' => 0
				])->first();

				if (!$previousInvitation) {
					$user = Auth::user();

					// Input to the invitations table
					DB::table($type . '_invitations')->insert([
						'inviter_id' => $user->id,
						'invitee' => $inviteeEmail,
						$type . '_id' => $data->id,
						'role_id' => $role->id,
						'token' => $token,
						'created_at' => Carbon::now(),
						'updated_at' => Carbon::now()
					]);

					dispatch(new ProcessInvitationEmail([
						'type' => $type,
						'name' => $data->name,
						'user_name' => $user->first_name . ' ' . $user->last_name,
						'role' => $role->name,
						'token' => $token,
						'email' => $inviteeEmail,
						'title' => "You have been invited to join the " . $type . " " . $data->name . " on Informed 365"
					]));
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
			'invitee' => strtolower($user->email),
			'token' => $token
		])->first();

		// Invalid invitation
		if (!$invitation) {
			return $this->returnErrorMessage(403, 'Invalid invitation.');
		} elseif($invitation->status === 1) {
			return $this->returnErrorMessage(403, 'Invitation has previously been accepted.');
		}

		if ($type == 'team') {
			$type_id = $invitation->team_id;
		} else {
			$type_id = $invitation->application_id;
		}

		$user_list = DB::table($type . '_users')->where([
			'user_id' => $user->id,
			$type . '_id' => $type_id
		])->first();

		// Check if user already exists in the Team or Application
		if (!$user_list) {
			if ($user_list = DB::table($type . '_users')->insert([
				'user_id' => $user->id,
				$type . '_id' => $type_id,
				'role_id' => $invitation->role_id,
				'created_at' => Carbon::now(),
				'updated_at' => Carbon::now()
			])) {
				// Remove token and update status at invitations table
				DB::table($type . '_invitations')->where('id', $invitation->id)->update([
					'status' => 1,
					'updated_at' => Carbon::now()
				]);
			}
		}

		if ($user_list) {
			if ($type == 'team') {
				$team = Team::find($type_id);

				$application_user = ApplicationUser::where([
					'user_id' => $user->id,
					'application_id' => $team->application_id
				])->first();

				if (!$application_user) {
                    $application_user = ApplicationUser::create([
                        'user_id' => $user->id,
                        'application_id' => $team->application_id,
                        'role_id' => Role::where('name', 'User')->first()->id
                    ]);
				}
			}

			return response()->json([
				'status' => 'success',
				'message' => 'Invitation has been successfully accepted.',
				'data' => $type == 'team' ? Team::find($type_id) : Application::find($type_id),
				'slug' => $type == 'team' ? Application::find(Team::find($type_id)->application_id)->slug : Application::find($type_id)->slug
			], 200);
		}

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

		$user_list = DB::table($type . '_users')->where([
			'user_id' => $user->id,
			$type . '_id' => $data->id
		])->first();

		// Check if user already exists in the Team or Application
		if (!$user_list) {
			$user_list = DB::table($type . '_users')->insert([
				'user_id' => $user->id,
				$type . '_id' => $data->id,
				'role_id' => Role::where('name', 'User')->first()->id,
				'created_at' => Carbon::now(),
				'updated_at' => Carbon::now()
			]);
		}

		if ($user_list) {
			if ($type == 'team') {
				$application_user = ApplicationUser::where([
					'user_id' => $user->id,
					'application_id' => $data->application_id
				])->first();

				if (!$application_user) {
                    $application_user = ApplicationUser::create([
                        'user_id' => $user->id,
                        'application_id' => $data->application_id,
                        'role_id' => Role::where('name', 'User')->first()->id
                    ]);
				}
			}

			return response()->json([
				'status' => 'success',
				'message' => 'You have joined the ' . $type . ' successfully.',
				'data' => $data,
				'slug' => $type == 'team' ? Application::find($data->application_id)->slug : Application::find($data->id)->slug
			], 200);
		}

		// Send error
		return $this->returnErrorMessage(503, 'You cannot join the ' . $type . ' right now. Please try again later.');
	}

	protected function getComparator($comparator, $value)
    {
        switch ($comparator) {
            case 'equals':
                $query = '=';
                break;
            case 'not equal to':
                $query = '!=';
                break;
            case 'less than':
                $query = '<';
                break;
            case 'greater than':
                $query = '>';
                break;
            case 'less than or equal to':
                $query = '<=';
                break;
            case 'greater than or equal to':
                $query = '>=';
                break;
            case 'contains':
                $query = 'LIKE';
                $value = '%' . $value . '%';
                break;
            case 'does not contain':
                $query = 'NOT LIKE';
                $value = '%' . $value . '%';
                break;
            case 'starts with':
                $query = 'LIKE';
                $value = $value . '%';
                break;
            case 'ends with':
                $query = 'LIKE';
                $value = '%' . $value;
                break;
            default:
                $query = $comparator;
        }

        return [
            'query' => $query,
            'value' => $value
        ];
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
			$path = Storage::disk('s3')->putFile('uploads', $request->file('file'));
			$url = Storage::disk('s3')->url($path);
			return $this->returnSuccessMessage('path', $url);
		} catch (Exception $e) {
			return $this->returnErrorMessage(503, $e->getMessage());
		}
	}
}
