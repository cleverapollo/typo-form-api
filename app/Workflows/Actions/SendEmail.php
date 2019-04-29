<?php

namespace App\Workflows\Actions;

use \Mail;
use App\Models\Invitation;
use App\Models\Invite;
use App\Models\WorkflowJob;
use App\Services\ApplicationService;

class SendEmail implements IAction {
    public function __construct()
    {
        $this->defaults = [
        ];
    }

    public function handle(WorkflowJob $job) 
    {
        // TODO this implementation is _way_ too bound to invite (it should be more generic)
        // $job->data should contain the email + meta (cc, bcc) not the invite itself..
        //
        // $config = array_merge($this->defaults, json_decode($job->workflow->action_config, true) ?? []);
        // ['cc' => $cc, 'bcc' => $bcc] = $config;

        $data = json_decode($job->data, true) ?? [];
        ['invite_id' => $inviteId] = $data;

        // TODO remove, see notes above
        $invite = \App\Models\Invitation::findOrFail($inviteId);

        // TODO potentially queue up as job..
        app(ApplicationService::class)->sendInvitationEmail([
            'invitation' => [
                'email' => $invite->email,
            ],
            'meta' => $invite->meta,
        ]);

        return "Email sent";
    }


}