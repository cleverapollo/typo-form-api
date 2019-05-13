<?php

namespace App\Workflows\Actions;

use \MailService;
use App\Models\WorkflowJob;
use App\Repositories\WorkflowRepositoryFacade as WorkflowRepository;
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

        MailService::send($data, [
            'email' => 'email',
            'body' => 'message',
            'subject' => 'subject',
            'cc' => 'cc',
            'bcc' => 'bcc',
        ], [
            'first_name' => 'first_name',
            'last_name' => 'last_name',
            'email' => 'email',
        ]);

        WorkflowRepository::completeJob($this->workflowJob);
    }

    public function failed(\Exception $exception)
    {
        WorkflowRepository::failJob($this->workflowJob, "{$exception->getMessage()} in {$exception->getFile()}:{$exception->getLine()}");
    }

}