<?php

namespace App\Workflows\Triggers;

use \UserStatusRepository;
use \WorkflowRepository;
use App\Models\ApplicationUser;
use App\Models\Workflow;
use App\Models\WorkflowJob;
use App\Repositories\WorkflowRepository as WorkflowRepositoryConstants;
use Carbon\Carbon;

class InviteTrigger implements ITrigger {
    public function getKey(): string
    {
        return 'Invite';
    }

    public function scheduleJobs(Workflow $workflow) 
    {
        // Collect all invitations for the application that is linked to workflow and then apply
        // the checks() to query. Checks is used during scheduling, unscheduling and pre-running
        // the job
        //
        $invites = ApplicationUser
            ::whereApplicationId($workflow->application_id)
            ->whereStatus(UserStatusRepository::idByLabel('Invited'));
        $invites = $this->check($workflow, $invites);
        $invites = $invites->get();


        // Ensure this job doesn't already exist. We are checking all jobs, including completed 
        // jobs - we don't want the same job being created everytime the existing one has completed
        //
        // This checking will vary from trigger to trigger, but will essentially follow this 
        // pattern. If multiple triggers end up following an identical pattern to the below, 
        // we can extract it out to a trait or util
        //
        $existingJobs = WorkflowRepository::jobsOfWorkflow($workflow);
        $invites = $invites->filter(function($invite) use ($workflow, $existingJobs) {
            $hasMatchingExistingJob = $existingJobs->first(function($existingJob) use ($invite) {
                return $existingJob->transaction_id === $invite->id;
            });
            return is_null($hasMatchingExistingJob);
        });

        $actionConfig = json_decode($workflow->action_config, true) ?? [];
        $invites->each(function($invite) use ($actionConfig, $workflow) {
            WorkflowRepository::createActiveJob(
                $invite->id,
                $workflow->id,
                $this->calculateScheduledFor($invite, $workflow),
                array_merge($actionConfig, [
                    'email' => $invite->user->email,
                    'first_name' => $invite->user->first_name,
                    'last_name' => $invite->user->last_name,
                ])
            );
        });

        return $invites->count();
    }

    public function isScheduled(WorkflowJob $job)
    {
        $invite = ApplicationUser::findOrFail($job->transaction_id);
        $scheduled = $job->scheduled_for->addMilliseconds($invite->workflow_delay);

        $now = Carbon::now()->toDateTimeString();

        return $scheduled < $now;
    }

    public function calculateScheduledFor($invite, $workflow)
    {
        return $invite->created_at->addMilliseconds($workflow->delay);
    }

    public function check(Workflow $workflow, $query) 
    {
        $config = json_decode($workflow->trigger_config, true) ?? [];

        // We want to extract out key config items to construct our check method. It is important
        // to note that an unset value is very different from a falsey value in this case. Unset
        // implies we don't "care" either way, whereas, a "false" means we want to look for 
        // exactly false
        //
        // TODO This switch-esk block could be simplified into a model whitelist, where a model
        // lists the attributes that can be checked against
        //
        if (isset($config['invitation_status'])) {
            $status = $config['invitation_status'] === true 
                ? UserStatusRepository::idByLabel('Active')
                : UserStatusRepository::idByLabel('Invited');
            $query->where('status', '=', $status);
        }

        // Limit invitations to the timeperiod set in workflow
        // TODO this actually will introduce a bug if the workflow from/to dates are changed 
        // multiple times, as it may cause a job to be canceled if it falls outside of these dates
        // Adding to https://trello.com/c/EAnvhIXH/67-workflow-v1-tech-debt
        $query->where('created_at', '>', $workflow->active_from);
        if(isset($workflow->active_to)) {
            $query->where('created_at', '<', $workflow->active_to);
        }

        return $query;
    }

    // Unscheduling jobs is primarily to keep the logs endpoint fresh. This can be called at a lower
    // frequency, and/or manually if the user requests to see the workflow logs
    //
    public function unscheduleJob(WorkflowJob $job)
    {
        $invite = ApplicationUser::whereId($job->transaction_id);
        $invite = $this->check($job->workflow, $invite);
        $invite = $invite->first();

        // If invite still matches the criteria, we don't need to cancel it
        if($invite && !$job->workflow->trashed()) {
            return false;
        }

        WorkflowRepository::cancelJob($job);
        return true;
    }
}
