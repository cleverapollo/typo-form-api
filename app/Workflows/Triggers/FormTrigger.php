<?php

namespace App\Workflows\Triggers;

use App\Models\Form;
use App\Models\WorkflowJob;
use App\Models\Workflow;
use Carbon\Carbon;

class FormTrigger implements ITrigger {
    public function scheduleJobs(Workflow $workflow) {
        // Check for all cases where this workflow is relevant
        // And doesn't already exist

        $forms = Form::whereNotNull('submitted_date')->get();

        // TODO move to repository
        $jobs = WorkflowJob::whereWorkflowId($workflow->id)->whereStatus(0)->get();

        // TODO needs to cross check against the check method 
        $needingJobs = $forms->whereNotIn('id', $jobs->map->transaction_id);

        $needingJobs
            ->each(function($form) use ($workflow) {
                // TODO move to repository
                // WorkflowJobRepository::createRunningJob($form, $workflow, $delayCalc (callback?))
                WorkflowJob::create([
                    'transaction_id' => $form->id,
                    'workflow_id' => $workflow->id,
                    'scheduled_for' => Carbon::parse($form->submitted_date)->addMinutes($workflow->delay),
                    'completed_at' => null,
                    'status' => 0,
                ]);
            });

        return $needingJobs->count();
    }

    public function unscheduleJobs() {
        // Forms don't "uncomplete" so don't need to unschedule them
    }

    public function check(){}
}