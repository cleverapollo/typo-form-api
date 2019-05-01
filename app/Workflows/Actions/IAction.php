<?php

namespace App\Workflows\Actions;

use App\Models\Workflow;
use App\Models\WorkflowJob;

interface IAction {
    public function handle();
}