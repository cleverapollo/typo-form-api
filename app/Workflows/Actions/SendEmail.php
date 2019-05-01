<?php

namespace App\Workflows\Actions;

use \Mail;
use App\Models\Invitation;
use App\Models\Invite;
use App\Models\WorkflowJob;
use App\Repositories\WorkflowRepositoryFacade as WorkflowRepository;
use App\Services\ApplicationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendEmail implements ShouldQueue, IAction {
    use InteractsWithQueue, Queueable, SerializesModels;

    protected $workflowJob;

    public function __construct(WorkflowJob $workflowJob)
    {
        $this->workflowJob = $workflowJob;
        $this->defaults = [];
    }

    public function handle() 
    {
        // TODO this implementation is _way_ too bound to invite (it should be more generic)
        // $job->data should contain the email + meta (cc, bcc) not the invite itself..
        //
        // $config = array_merge($this->defaults, json_decode($job->workflow->action_config, true) ?? []);
        // ['cc' => $cc, 'bcc' => $bcc] = $config;
        
        $data = json_decode($this->workflowJob->data, true) ?? [];
        ['invite_id' => $inviteId] = $data;

        // TODO remove, see notes above
        $invite = \App\Models\Invitation::findOrFail($inviteId);

        app(ApplicationService::class)->sendInvitationEmail([
            'invitation' => [
                'email' => $invite->email,
            ],
            'meta' => $invite->meta,
        ]);

        WorkflowRepository::completeJob($this->workflowJob);
    }

    public function failed(\Exception $exception)
    {
        WorkflowRepository::failJob($this->workflowJob, "{$exception->getMessage()} in {$exception->getFile()}:{$exception->getLine()}");
    }

}