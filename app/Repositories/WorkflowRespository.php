<?php

namespace App\Repositories;

use App\Models\Workflow;
use App\Models\WorkflowJob;
use App\Repositories\ApplicationRepositoryFacade as ApplicationRepository;
use App\Workflows\WorkflowHelpers;
use App\User;
use Carbon\Carbon;

class WorkflowRepository {
    const JOB_STATUS_ACTIVE = 1;
    const JOB_STATUS_SUCCESSFUL = 2;
    const JOB_STATUS_CANCELED = 4;
    const JOB_STATUS_FAILURE = 8;
    const JOB_STATUS_QUEUED = 16;

    const WORKFLOW_STATUS_ACTIVE = 1;
    const WORKFLOW_STATUS_PAUSED = 2;

    public function createActiveJob($transactionId, $workflowId, $scheduledFor, $data = [])
    {
        return WorkflowJob::create([
            'transaction_id' => $transactionId,
            'workflow_id' => $workflowId,
            'scheduled_for' => $scheduledFor,
            'completed_at' => null,
            'status' => self::JOB_STATUS_ACTIVE,
            'data' => json_encode($data),
        ]);
    }

    public function all($user, $application)
    {
        return Workflow::whereApplicationId($application->id)->get();
    }

    public function byId($user, $application, $id)
    {
        return Workflow::whereId($id)
            ->whereApplicationId($application->id)
            ->firstOrFail();
    }

    public function activeWorkflows()
    {
        $now = Carbon::now()->toDateTimeString();
        return Workflow::whereStatus(self::WORKFLOW_STATUS_ACTIVE)
            ->where('active_from', '<', $now)
            ->where(function ($query) use ($now) {
                $query->where('active_to', '>', $now)
                    ->orWhereNull('active_to');
            })
            ->get();
    }

    public function jobsOfWorkflow(Workflow $workflow)
    {
        return WorkflowJob::whereWorkflowId($workflow->id)->get();
    }

    /**
     * Get all active jobs. This is useful for descheduling (cleanup). 
     */
    public function activeJobs() 
    {
        // We are getting all jobs regardless of their parent workflows status as we want to give
        // the descheduling the chance to check the trigger (which is only accessible via the 
        // workflow)
        //
        return WorkflowJob
            ::with(['workflow' => function($q) {
                $q->withTrashed();
            }])
            ->whereStatus(self::JOB_STATUS_ACTIVE)->get();
    }

    public function jobsToBeProcessed()
    {
        $jobs = WorkflowJob::with('workflow')
            ->whereStatus(self::JOB_STATUS_ACTIVE)
            ->whereHas('workflow', function($workflowQuery) {
                $workflowQuery->whereStatus(self::WORKFLOW_STATUS_ACTIVE);
                $workflowQuery->whereNull('deleted_at');
             })
            ->get();

        $jobs = $jobs->filter(function($job) {
            $trigger = WorkflowHelpers::resolveTrigger($job->workflow->trigger);
            return $trigger->isScheduled($job);
        });

        return $jobs;
    }

    public function cancelJob(WorkflowJob $job)
    {
        $job->completed_at = Carbon::now()->toDateTimeString();
        $job->status = self::JOB_STATUS_CANCELED;
        $job->save();
        return $job;
    }

    public function completeJob(WorkflowJob $job)
    {
        $job->completed_at = Carbon::now()->toDateTimeString();
        $job->status = self::JOB_STATUS_SUCCESSFUL;
        $job->save();
        return $job;
    }

    public function failJob(WorkflowJob $job, $message = '')
    {
        $job->completed_at = Carbon::now()->toDateTimeString();
        $job->status = self::JOB_STATUS_FAILURE;
        $job->message = $message;
        $job->save();
        return $job;
    }

    public function queueJob(WorkflowJob $job)
    {
        $job->status = self::JOB_STATUS_QUEUED;
        $job->save();
        return $job;
    }

    public function isJobActive(WorkflowJob $job)
    {
        return $job->status === self::JOB_STATUS_ACTIVE;
    }

    public function isWorkflowActive(Workflow $workflow)
    {
        return $workflow->status === self::WORKFLOW_STATUS_ACTIVE && !$workflow->trashed();
    }
}