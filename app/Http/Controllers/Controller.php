<?php

namespace App\Http\Controllers;

use Auth;
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
                while (!is_null(DB::table($type . '_invitations')->where('token', $token)->first())) {
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
                        'token' => $token
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
        $invitation = DB::table($type . '_invitations')->where([
            'token' => $token,
            'status' => 0
        ])->first();

        // Send error if token does not exist
        if (!$invitation) {
            return response()->json([
                'status' => 'fail',
                'message' => 'Invalid token.'
            ], 404);
        }

        // Send request to create if the user is not registered
        $user = User::where('email', $invitation->invitee)->first();
        if (!$user) {
            return response()->json([
                'status' => 'success',
                'message' => 'User need to create account.'
            ], 201);
        }

        if (DB::table($type . '_users')->create([
            'user_id' => $user->id,
            $type . '_id' => $invitation[$type . '_id'],
            'role' => $invitation->role
        ])) {
            $invitation->token = null;
            $invitation->status = 1;
            $invitation->save();

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
}
