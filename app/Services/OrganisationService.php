<?php

namespace App\Services;

use App\Models\Invitation;
use Illuminate\Support\Facades\Mail;

class OrganisationService extends Service {
    /**
     * Checks for user inviation
     *
     * @param String $email
     * @param Int $reference_id
     * @return boolean
     */
    public function hasInvitation ($email, $reference_id) {
        return !empty($this->getInvitation($email, $reference_id));
    }

    /**
     * Get user invitation
     *
     * @param String $email
     * @param Int $reference_id
     * @return void
     */
    public function getInvitation($email, $reference_id) {
        return Invitation::where('email', $email)
            ->where('reference_id', $reference_id)
            ->first();
    }

    /**
     * Create user invitation
     *
     * @param array $data
     * @return void
     */
    public function inviteUser ($data) {
        if(!$this->hasInvitation($data['invitation']['email'], $data['organisation_id'])) {
            Invitation::create([
                'inviter_id' => $data['user_id'],
                'email' => $data['invitation']['email'],
                'first_name' => $data['invitation']['firstname'],
                'last_name' => $data['invitation']['lastname'],
                'meta' => $data['meta'],
                'role_id' => $data['role_id'],
                'type_id' => $data['type_id'],
                'reference_id' => $data['organisation_id']
            ]);
        }

        $this->sendInvitationEmail($data);
    }

    /**
     * Send user invitation email
     *
     * @param array $data
     * @return void
     */
    public function sendInvitationEmail ($data) {
        Mail::send([], [], function ($message) use ($data) {
            $message
                ->to($data['invitation']['email'])
                ->from(ENV('MAIL_FROM_ADDRESS'))
                ->subject($data['meta']['subject'])
                ->setBody($data['meta']['message'], 'text/html');
            
            // Optional CC
            if (!empty($data['meta']['cc'])) {
                $message->cc($this->formatEmailAddresses($data['meta']['cc']));
            }

            // Optional BCC
            if (!empty($data['meta']['bcc'])) {
                $message->bcc($this->formatEmailAddresses($data['meta']['bcc']));
            }
        });
    }
}