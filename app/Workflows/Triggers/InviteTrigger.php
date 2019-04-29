<?php

namespace App\Workflows\Triggers;

use App\Models\Invitation;
use App\Models\Workflow;
use App\Models\WorkflowJob;
use App\Repositories\WorkflowRepositoryFacade as WorkflowRepository;
use Carbon\Carbon;

class InviteTrigger implements ITrigger {
    public function scheduleJobs(Workflow $workflow) {
        // TODO should be using invitation foreign status instead of hard coded false..
        $invites = Invitation::whereStatus(FALSE)->get();
        $jobs = WorkflowRepository::activeJobsOfWorkflow($workflow);

        // TODO needs to cross check against the check method 
        $invitesNeedingJobs = $invites->whereNotIn('id', $jobs->map->transaction_id);

        $invitesNeedingJobs
            ->each(function($invite) use ($workflow) {
                $scheduledFor = $invite->updated_at->addMinutes($workflow->delay);
                WorkflowRepository::createActiveJob($invite->id, $workflow->id, $scheduledFor, [
                    'invite_id' => $invite->id,
                ]);
            });

        return $invitesNeedingJobs->count();
    }

    public function unscheduleJobs() {
        Invitation::updated(function($invite) {
            if($invite->status !== true) {
                return;
            }

            WorkflowRepository::activeJobsOfTrigger('Invite', $invite->id)->map(function($job) {
                WorkflowRepository::cancelJob($job);
            });
        });
    }

    public function check(){}
}
