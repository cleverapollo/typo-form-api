<?php

namespace App\Workflows\Actions;

use \MailService;
use App\User;
use App\Models\Note;
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

        // TODO BLOCK
        // This is somewhat hardcoded, This doesn't really belong here...... 
        // https://trello.com/c/EAnvhIXH/67-workflow-v1-tech-debt
        // 
        $user = User::whereEmail($data->email)->first();
        $workflow = $this->workflowJob->workflow;
        Note::unguard();
        $note = Note::create([
            'application_id' => $workflow->application_id,
            'note_type_id' => 3, // <-- "Other"
            'description' => __('app.workflow_email_note_description', ['userName' => $user->first_name, 'workflowName' => $workflow->name]),
            'note' => '',
            'user_id' => $workflow->author_id,
            'recordable_id' => $user->id,
            'recordable_type' => 'User',
        ]);
        Note::reguard();
        // END TODO BLOCK

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