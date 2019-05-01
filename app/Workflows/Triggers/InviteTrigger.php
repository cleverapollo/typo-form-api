<?php

namespace App\Workflows\Triggers;

use App\Models\Invitation;
use App\Models\Workflow;
use App\Models\WorkflowJob;
use App\Repositories\WorkflowRepositoryFacade as WorkflowRepository;
use Carbon\Carbon;

class InviteTrigger implements ITrigger {
    public function scheduleJobs(Workflow $workflow) {
        // Collect all invitations for the application that is linked to workflow and then apply
        // the checks() to query. Checks is used during scheduling, unscheduling and pre-running
        // the job
        //
        $invites = Invitation::whereReferenceId($workflow->application_id);
        $invites = $this->check($invites);
        $invites = $invites->get();

        // Ensure this job doesn't already exist. We are checking all jobs, including completed 
        // jobs - we don't want the same job being created everytime the existing one has completed
        //
        // This checking will vary from trigger to trigger, but will essentially follow this 
        // pattern. If multiple triggers end up following an indentical pattern to the below, 
        // we can extract it out to a trait or util
        //
        $existingJobs = WorkflowRepository::jobsOfWorkflow($workflow);
        $invites = $invites->filter(function($invite) use ($workflow, $existingJobs) {
            $hasMatchingExistingJob = $existingJobs->first(function($existingJob) use ($invite) {
                return $existingJob->transaction_id === $invite->id;
            });
            return is_null($hasMatchingExistingJob);
        });

        $invites->each(function($invite) use ($workflow) {
            WorkflowRepository::createActiveJob(
                $invite->id,
                $workflow->id,
                $this->calculateScheduledFor($invite, $workflow),
                [ 'invite_id' => $invite->id ] // <-- TODO Still need to decouple invite from the job
            );
        });

        return $invites->count();
    }

    public function calculateScheduledFor($invite, $workflow)
    {
        return $invite->created_at->addMinutes($workflow->delay);
    }

    public function check($query) {
        // TODO should be using invitation foreign status instead of hard coded false..
        $query->whereStatus(FALSE);
        return $query;
    }

    public function unscheduleJobs() {
        // TODO - don't like this. Most likely drop this and just leverage check method
        // TODO - remove workflow service provider too
        Invitation::updated(function($invite) {
            if($invite->status !== true) {
                return;
            }

            WorkflowRepository::activeJobsOfTrigger('Invite', $invite->id)->map(function($job) {
                WorkflowRepository::cancelJob($job);
            });
        });
    }
}
