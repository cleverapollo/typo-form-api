<?php

namespace App\Workflows\Actions;

use \Storage;
use App\Models\Invitation;
use App\Models\Workflow;
use App\Models\WorkflowJob;

// DEMO Action only - can be used to test quick synchronous actions and triggers
class AppendFile implements IAction {
    public function __construct()
    {
        $this->defaults = [
            'to' => '[not set]',
        ];
    }

    public function handle(WorkflowJob $job) 
    {
        $config = array_merge($this->defaults, json_decode($job->workflow->action_config, true) ?? []);
        ['to' => $to] = $config;

        Storage::append('Workflow--AppendsFile.log', "JOB {$job->id}: $to");

        return "AppendFile written";
    }

    
}