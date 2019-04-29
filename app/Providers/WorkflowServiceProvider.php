<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class WorkflowServiceProvider extends ServiceProvider {
    public function boot()
    {
        // TODO better addition of registered "listeners"
        (new \App\Workflows\Triggers\InviteTrigger())->unscheduleJobs();
    }
}