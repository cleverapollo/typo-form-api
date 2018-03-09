<?php

namespace App\Http\Controllers;

use Laravel\Lumen\Routing\Controller as BaseController;
use App\Http\Foundation\Auth\Access\AuthorizesRequests;

class Controller extends BaseController
{
    use AuthorizesRequests;

    /**
     * Generate error message for status and action
     *
     * @param $data
     * @param $status
     * @param $action
     * @return string
     */
    protected function generateErrorMessage($data, $status, $action) {
        if ($status == 404) {
            return 'There is no ' . $data . ' with this ID.';
        }
        return 'Failed to ' . $action . ' '. $data . '. Please try again later.';
    }

    /**
     * Send invitation
     *
     * @param $type
     * @param $data
     * @param $user
     * @param $invitations
     */
    protected function sendInvitation($type, $data, $user, $invitations)
    {
        if ($invitations && count($invitations) > 0) {
            foreach ($invitations as $invitation) {
                $token = base64_encode(str_random(40));
                while (!is_null(DB::table($type . '_invitations')->where('token', $token)->first())) {
                    $token = base64_encode(str_random(40));
                }

                // Send email to the invitee
                Mail::send('emails.invitation', [
                    'type' => $type,
                    'name' => $data->name,
                    'userName' => $user->first_name . " " . $user->last_name,
                    'role' => $invitation->role,
                    'token' => $token
                ], function ($message) use ($invitation) {
                    $message->from('info@informed365.com', 'Informed 365');
                    $message->to($invitation->email);
                });

                // Input to the invitations table
                DB::table($type . '_invitations')->insert([
                    'inviter_id' => $user->id,
                    'invitee' => $invitation->email,
                    $type . '_id' => $data->id,
                    'role' => $invitation->role,
                    'token' => $token
                ]);
            }
        }
    }
}
