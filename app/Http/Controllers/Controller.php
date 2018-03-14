<?php

namespace App\Http\Controllers;

use Auth;
use Carbon\Carbon;
use App\User;
use App\Http\Foundation\Auth\Access\AuthorizesRequests;
use Laravel\Lumen\Routing\Controller as BaseController;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class Controller extends BaseController
{
    use AuthorizesRequests;

    /**
     * Generate error message for status and action.
     *
     * @param  $data
     * @param  $status
     * @param  $action
     * @return string
     */
    protected function generateErrorMessage($data, $status, $action)
    {
        if ($status == 404) {
            return 'There is no ' . $data . ' with this ID.';
        }
        return 'Failed to ' . $action . ' ' . $data . '. Please try again later.';
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
            $user = Auth::user();
            foreach ($invitations as $invitation) {
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

                    if ($isIncluded) {
                        continue;
                    }
                }

                // Check if the user is already invited
                $previousInvitation = DB::table($type . '_invitations')->where([
                    'invitee' => $invitation['email'],
                    $type . '_id' => $data->id
                ])->first();

                if (!$previousInvitation) {
                    // Input to the invitations table
                    DB::table($type . '_invitations')->insert([
                        'inviter_id' => $user->id,
                        'invitee' => $invitation['email'],
                        $type . '_id' => $data->id,
                        'role' => $invitation['role'],
                        'token' => $token,
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now()
                    ]);

                    // Send email to the invitee
                    Mail::send('emails.invitation', [
                        'type' => $type,
                        'name' => $data->name,
                        'userName' => $user->first_name . " " . $user->last_name,
                        'role' => $invitation['role'],
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
            return response()->json([
                'status' => 'fail',
                'message' => 'Invalid token.'
            ], 403);
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
	        return response()->json([
		        'status' => 'fail',
		        'message' => 'User is already included in the ' . $type
	        ], 403);
        }

        if (DB::table($type . '_users')->insert([
            'user_id' => $user->id,
            $type . '_id' => $dataId,
            'role' => $invitation->role,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
        ])) {
        	// Remove token and update status at invitations table
            DB::table($type . '_invitations')->where('id', $invitation->id)->update([
                'token' => null,
                'status' => 1,
                'updated_at' => Carbon::now()
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Invitation has been successfully accepted.'
            ], 200);
        };

        // Send error
        return response()->json([
            'status' => 'fail',
            'message' => 'You cannot accept the invitation now. Please try again later.'
        ], 503);
    }

	/**
	 * Accept invitation request.
	 *
	 * @param $type
	 * @param $token
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
			return response()->json([
				'status' => 'fail',
				'message' => 'Invalid token.'
			], 403);
		}

		if ($type == 'team') {
			$dataId = $data->team_id;
		} else {
			$dataId = $data->application_id;
		}

		// Send error if user already exists in the Team or Application
		if (DB::table($type . '_users')->where([
			'user_id' => $user->id,
			$type . '_id' => $dataId
		])->first()) {
			return response()->json([
				'status' => 'fail',
				'message' => 'User is already included in the ' . $type
			], 403);
		}

		if (DB::table($type . '_users')->insert([
			'user_id' => $user->id,
			$type . '_id' => $dataId,
			'created_at' => Carbon::now(),
			'updated_at' => Carbon::now()
		])) {
			return response()->json([
				'status' => 'success',
				'message' => 'You have joined the ' . $type . ' successfully.'
			], 200);
		};

		// Send error
		return response()->json([
			'status' => 'fail',
			'message' => 'You cannot join the ' . $type . ' right now. Please try again later.'
		], 503);
	}
}
