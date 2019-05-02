<?php

namespace App\Workflows\Triggers;

use App\Models\Workflow;
use App\Models\WorkflowJob;

interface ITrigger {
    public function getKey(): string;
    public function scheduleJobs(Workflow $workflow);
    public function unscheduleJob(WorkflowJob $job);
    public function check(Workflow $workflow, $query);
}