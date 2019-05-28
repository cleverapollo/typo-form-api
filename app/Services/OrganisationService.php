<?php

namespace App\Services;

use \ApplicationRepository;
use \ApplicationUserRepository;
use \MailService;
use \OrganisationUserRepository;
use \RoleRepository;
use \UserRepository;
use \UserStatusRepository;
use \UrlService;
use App\User;
use App\Models\Application;
use Carbon\Carbon;

class OrganisationService extends Service {

    public function acceptInvitation($slug, $user) {
        $user = $user->resource;
        $application = Application::whereSlug($slug)->first();

        if (!$application) {
            return;
        }

        $invitations = OrganisationUserRepository::invitations($application->id, $user->id);

        $invitations->each(function($invitation) use ($application, $user) {
            $invitation->status = UserStatusRepository::idByLabel('Active');
            $invitation->save();

            ApplicationUserRepository::addActiveUser($application->id, $user->id, RoleRepository::idByName('User'));
        });
    }

    /**
     * Create user invitation
     *
     * @param array $data
     * @return void
     */
    public function inviteUser ($data) {
        $email = strtolower($data['invitation']['email']);
        $user = User::whereEmail($email)->first();
        $isExistingUser = !is_null($user);

        // If the user hasn't registered in the platform, in any application, add them first. 
        if(!$isExistingUser) {
            $user = UserRepository::createUnregisteredUser($data['invitation']['firstname'], $data['invitation']['lastname'], $email, $data['role_id']);
        }

        // If the user is not in the organisation (neither invited nor active), then we send them
        // an invite. We don't want to send invites to already registed, already in organisation
        // users
        //
        if(!OrganisationUserRepository::isUserInOrganisation($data['organisation_id'], $user->id)) {
            OrganisationUserRepository::inviteUser($data['organisation_id'], $user->id, $data['role_id'], $data['user_id'], $data['meta'], $data['invitation']['meta']);
            $application = Application::findOrFail($data['application_id']);

            $this->sendInvitationEmail($data, $application, $isExistingUser);
        }
    }

    /**
     * Send user invitation email
     *
     * @param array $data
     * @return void
     */
    public function sendInvitationEmail ($data, $application, $isExistingUser) {
        MailService::send($data, [
            'email' => 'invitation.email',
            'body' => 'meta.message',
            'subject' => 'meta.subject',
            'cc' => 'meta.cc',
            'bcc' => 'meta.bcc',
        ], [
            'first_name' => 'invitation.firstname',
            'last_name' => 'invitation.lastname',
            'email' => 'invitation.email',
            'invite_link' => function() use ($data, $application, $isExistingUser) {
                $query = [
                    'firstname' => data_get($data, 'invitation.firstname'),
                    'lastname' => data_get($data, 'invitation.lastname'),
                    'email' => data_get($data, 'invitation.email')
                ];
                $link = $isExistingUser 
                    ? UrlService::getApplicationLogin($application, $query, true)
                    : UrlService::getApplicationRegister($application, $query, true);
                return "<a href='$link' target='_blank'>$link</a>";
            }
        ]);
    }
}