<?php

namespace App\Services;

use Carbon\Carbon;
use App\Events\InvitationAccepted;
use Illuminate\Support\Facades\Mail;
use App\Models\Application;
use App\Models\ApplicationUser;
use App\Models\Organisation;
use App\Models\OrganisationUser;
use App\Models\Invitation;
use App\Models\Type;
use App\Models\Role;
use App\User;

class OrganisationService extends Service {

    public function acceptInvitation($slug, $user) {
        $application = Application::where('slug', $slug)->first();
        $organisations = $application->organisations->pluck('id')->all();
        $type = Type::where('name', 'organisation')->first();

        $invitations = Invitation::where([
            'email' => strtolower($user->email),
            'type_id' => $type->id,
            'status' => false
        ])->whereIn('reference_id', $organisations)->get();

        foreach ($invitations as $invitation) {
            $user_list = OrganisationUser::where([
                'user_id' => $user->id,
                'organisation_id' => $invitation->reference_id
            ])->first();

            // Check if user already exists in the Organisation
            if (!$user_list) {
                if ($user_list = OrganisationUser::insert([
                    'user_id' => $user->id,
                    'organisation_id' => $invitation->reference_id,
                    'role_id' => $invitation->role_id,
                    'meta' => json_encode($invitation->meta),
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now()
                ])) {
                    // Remove token and update status at invitations table
                    Invitation::where('id', $invitation->id)->update([
                        'status' => 1,
                        'updated_at' => Carbon::now()
                    ]);

                    // Check for application access
                    if($organisation = Organisation::where('id', $invitation->reference_id)->first()) {
                        if(!$application_user = ApplicationUser::where(['user_id' => $user->id, 'application_id' => $organisation->application_id])->first()) {
                            ApplicationUser::insert([
                                'user_id' => $user->id,
                                'application_id' => $application->id,
                                'role_id' => Role::where('name', 'User')->first()->id
                            ]);
                        }
                    }

                    $userInstance = User::find($user->id);
                    event(new InvitationAccepted($userInstance, $invitation));
                }
            }
        }
    }

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
     * @return array \App\Models\Invitation
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