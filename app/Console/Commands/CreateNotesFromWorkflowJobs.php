<?php

namespace App\Console\Commands;

use \MailService;
use App\Models\Note;
use App\Models\Workflow;
use App\Models\WorkflowJob;
use App\User;
use Illuminate\Console\Command;

class CreateNotesFromWorkflowJobs extends Command {
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'informed365:create-notes-from-workflow-jobs';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "A one off task to create notes from workflow jobs";

    public function handle()
    {
        if (!$this->confirm('This should only be ran ONCE per environment. Do you wish to continue?')) {
            return;
        }

        WorkflowJob::whereStatus(2)->get()->each(function($workflowJob) {
            $data = json_decode($workflowJob->data) ?? [];
            $user = User::whereEmail($data->email)->first();
            $workflow = Workflow::withTrashed()->find($workflowJob->workflow_id);

            if(!$workflow) {
                dump($workflowJob->id . ': no workflow!');
                return;
            }
            if(!$user) {
                dump($workflowJob->id . ': no user!');
                return;
            }

            $mailData = MailService::applyMailMerge($data->subject, $data->message, $data, [
                'first_name' => 'first_name',
                'last_name' => 'last_name',
                'email' => 'email',
            ]);
            Note::unguard();
            $note = Note::create([
                'application_id' => $workflow->application_id,
                'note_type_id' => 2, // <-- "Email"
                'description' => $workflow->name,
                'note' => implode(PHP_EOL.PHP_EOL, [
                    'Subject: ' . $mailData['subject'],
                    'Body:',
                    html_to_plain_text($mailData['body']),
                 ]),
                'user_id' => $workflow->author_id,
                'recordable_id' => $user->id,
                'recordable_type' => 'User',
                'created_at' => $workflowJob->completed_at,
                'updated_at' => $workflowJob->completed_at,
            ]);
            Note::reguard();
        });
    }
}