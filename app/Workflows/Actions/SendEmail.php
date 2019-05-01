<?php

namespace App\Workflows\Actions;

use \Mail;
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
    }

    public function handle() 
    {
        $data = json_decode($this->workflowJob->data) ?? [];

        // TODO validate everything needed is available (email, subject, etc)

        app(ApplicationService::class)->sendInvitationEmail([
            'invitation' => [
                'email' => $data->email,
            ],
            'meta' => [
                'subject' => $data->subject ?? '',
                'message' => $data->message ?? '',
                'cc' => $data->cc ?? null,
                'bcc' => $data->bcc ?? null,
            ],
        ]);

        WorkflowRepository::completeJob($this->workflowJob);
    }

    public function failed(\Exception $exception)
    {
        WorkflowRepository::failJob($this->workflowJob, "{$exception->getMessage()} in {$exception->getFile()}:{$exception->getLine()}");
    }

}