<?php

namespace App\Workflows\Triggers;

use App\Models\Workflow;

interface ITrigger {
    public function scheduleJobs(Workflow $workflow);
    public function unscheduleJobs();
    public function check();
}