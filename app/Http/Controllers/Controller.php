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
	 * Return JSON response
	 *
	 * @param array $data
	 * @param string $status
	 * @param integer $code
	 * @return \Illuminate\Http\JsonResponse
	 */
	protected function jsonResponse($data, $status = 200) {
		return response()
			->json($data, $status);
	}

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
	protected function sendInvitation($type_name, $data, $invitations, $host, $role_id)
	{
        $type = Type::where('name', $type_name)->first();
        if (!$type) return;
        $role = Role::find($role_id);
        if (!$role) return;
		if ($invitations && count($invitations) > 0) {
			foreach ($invitations as $invitation) {
				// Check whether the role exists or not

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
					'email' => $inviteeEmail,
					'reference_id' => $data->id,
					'status' => false,
                    'type_id' => $type->id
				])->first();

				if (!$previousInvitation) {
					$user = Auth::user();

					// Input to the invitations table
					Invitation::insert([
						'inviter_id' => $user->id,
						'first_name' => $invitation['first_name'],
                        'last_name' => $invitation['last_name'],
						'email' => $inviteeEmail,
                        'meta' => $invitation['meta'],
						'reference_id' => $data->id,
						'role_id' => $role->id,
						'created_at' => Carbon::now(),
						'updated_at' => Carbon::now(),
                        'type_id' => $type->id
					]);

					if ($type->name == 'application') {
					    $link = $data->slug . '.' . $host;
                    }
                    else {
                        $application = Application::where('id', $data->application_id)->first();
					    $link = $application->slug . '.' . $host . '/organisations/' . $data->id;
                    }

					dispatch(new ProcessInvitationEmail([
						'type' => $type->name,
						'name' => $data->name,
						'link' => $link,
						'email' => $inviteeEmail,
						'title' => "You have been invited to join the " . $data->name . " " . $type->name . " on Informed 365"
					]));
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
	
	public function getApplication($user, $application_slug) {
		$application = $user->role->name === 'Super Admin' ? Application::where('slug', $application_slug)->first() : $user->applications()->where('slug', $application_slug)->first();
		
		return $application;
	}

	public function isUserApplicationAdmin($user, $application) {
		$is_user_application_admin = $user->role->name === 'Super Admin' || $this->getUserApplicationRole($user, $application) === 'Admin';

		return $is_user_application_admin;
	}

	public function getUserApplicationRole($user, $application) {
		$role = ApplicationUser::where([
			'user_id' => $user->id,
			'application_id' => $application->id
		])->first()->role;

		return $role->name ?? false;
	}
}
