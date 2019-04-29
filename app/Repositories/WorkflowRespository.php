<?php

namespace App\Repositories;

use App\Models\Workflow;
use App\Models\WorkflowJob;
use App\User;
use Carbon\Carbon;

class WorkflowRepository {
    // TODO _Possibly_ replace with foreign table if required. Not positive having a seperate 
    // table just for status labels is _really_ required
    const JOB_STATUS_ACTIVE = 1;
    const JOB_STATUS_SUCCESSFUL = 2;
    const JOB_STATUS_CANCELED = 4;
    const JOB_STATUS_FAILURE = 8;
    
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

    public function activeWorkflows()
    {
        // TODO need to limit to application and\or link application!
        $now = Carbon::now()->toDateTimeString();
        return Workflow::whereStatus(self::WORKFLOW_STATUS_ACTIVE)
            ->where('active_from', '<', $now)
            ->where('active_to', '>', $now)
            ->get();
    }

    public function activeJobsOfWorkflow(Workflow $workflow)
    {
        return WorkflowJob
            ::whereWorkflowId($workflow->id)
            ->whereStatus(self::JOB_STATUS_ACTIVE)
            ->get();
    }

    public function jobsToBeProcessed()
    {
        $now = Carbon::now()->toDateTimeString();
        // -> where workflow still active ?
        // application checks!
        return WorkflowJob::with('workflow')
            ->whereStatus(self::JOB_STATUS_ACTIVE)
            ->where('scheduled_for', '<', $now)
            ->get();
    }

    public function activeJobsOfTrigger($trigger, $transactionId)
    {
        return WorkflowJob
            ::whereHas('workflow', function($q) use ($trigger) {
                $q->whereTrigger($trigger);
            })
            ->whereStatus(self::JOB_STATUS_ACTIVE)
            ->whereTransactionId($transactionId)
            ->get();
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

    public function failJob(WorkflowJob $job)
    {
        $job->completed_at = Carbon::now()->toDateTimeString();
        $job->status = self::JOB_STATUS_FAILURE;
        $job->save();
        return $job;
    }
}