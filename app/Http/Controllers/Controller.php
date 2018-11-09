<?php

namespace App\Http\Controllers;

use Auth;
use Carbon\Carbon;
use App\User;
use App\Models\Application;
use App\Models\Organisation;
use App\Models\ApplicationUser;
use App\Models\Role;
use App\Models\Type;
use App\Models\Invitation;
use App\Http\Foundation\Auth\Access\AuthorizesRequests;
use App\Jobs\ProcessInvitationEmail;
use Laravel\Lumen\Routing\Controller as BaseController;
use Illuminate\Support\Facades\DB;

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
	 * @param $type_name
	 * @param $data
	 * @param $invitations
	 */
	protected function sendInvitation($type_name, $data, $invitations)
	{
        $type = Type::where('name', $type_name)->first();
        if (!$type) return;
		if ($invitations && count($invitations) > 0) {
			foreach ($invitations as $invitation) {
				// Check whether the role exists or not
				$role = Role::find($invitation[$type->name . '_role_id']);
				if (!$role) continue;

				$inviteeEmail = strtolower($invitation['email']);

				// Check if user is already included in the Organisation or Application
				$invitee = User::where('email', $inviteeEmail)->first();
				if ($invitee) {
					$isIncluded = DB::table($type->name . '_users')->where([
						'user_id' => $invitee->id,
						$type->name . '_id' => $data->id
					])->first();

					if ($isIncluded) continue;
				}

				// Check if the user is already invited
				$previousInvitation = Invitation::where([
					'invitee' => $inviteeEmail,
					'reference_id' => $data->id,
					'status' => 0,
                    'type_id' => $type->id
				])->first();

				if (!$previousInvitation) {
					$user = Auth::user();

					// Input to the invitations table
					Invitation::insert([
						'inviter_id' => $user->id,
						'invitee' => $inviteeEmail,
						'reference_id' => $data->id,
						'role_id' => $role->id,
						'created_at' => Carbon::now(),
						'updated_at' => Carbon::now(),
                        'type_id' => $type->id
					]);

					$link = '';
					if ($type->name == 'application') {
					    $link = $data->slug . '.' . config('mail.fronturl');
                    }
                    else {
                        $application = Application::where('id', $data->application_id)->first();
					    $link = $application->slug . '.' . config('mail.fronturl') . '/organisations/' . $data->id;
                    }

					dispatch(new ProcessInvitationEmail([
						'type' => $type->name,
						'name' => $data->name,
						'link' => $link,
						'email' => $inviteeEmail,
						'title' => "You have been invited to join the " . $type->name . " " . $data->name . " on Informed 365"
					]));
				}
			}
		}
	}

	/**
	 * Accept invitation request.
	 *
	 * @param $type_name
	 *
	 */
	protected function acceptInvitation($type_name)
	{
		$user = Auth::user();
        $type = Type::where('name', $type_name)->first();
		$invitations = Invitation::where([
			'invitee' => strtolower($user->email),
			'type_id' => $type->id,
			'status' => 0
		])->get();

        foreach ($invitations as $invitation) {
            $reference_id = $invitation->reference_id;

            $user_list = DB::table($type->name . '_users')->where([
                'user_id' => $user->id,
                $type->name . '_id' => $reference_id
            ])->first();

            // Check if user already exists in the Organisation or Application
            if (!$user_list) {
                if ($user_list = DB::table($type->name . '_users')->insert([
                    'user_id' => $user->id,
                    $type->name . '_id' => $reference_id,
                    'role_id' => $invitation->role_id,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now()
                ])) {
                    // Remove token and update status at invitations table
                    Invitation::where('id', $invitation->id)->update([
                        'status' => 1,
                        'updated_at' => Carbon::now()
                    ]);
                }
            }

            if ($user_list) {
                if ($type->name == 'organisation') {
                    $organisation = Organisation::find($reference_id);

                    $application_user = ApplicationUser::where([
                        'user_id' => $user->id,
                        'application_id' => $organisation->application_id
                    ])->first();

                    if (!$application_user) {
                        ApplicationUser::create([
                            'user_id' => $user->id,
                            'application_id' => $organisation->application_id,
                            'role_id' => Role::where('name', 'User')->first()->id
                        ]);
                    }
                }
            }
        }
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

		// Check if user already exists in the Organisation or Application
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
			if ($type == 'organisation') {
				$application_user = ApplicationUser::where([
					'user_id' => $user->id,
					'application_id' => $data->application_id
				])->first();

				if (!$application_user) {
                    ApplicationUser::create([
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
				'slug' => $type == 'organisation' ? Application::find($data->application_id)->slug : Application::find($data->id)->slug
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
}
