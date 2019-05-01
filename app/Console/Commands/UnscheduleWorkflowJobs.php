<?php

namespace App\Console\Commands;

use \App\Models\WorkflowJob;
use \App\Repositories\WorkflowRepositoryFacade as WorkflowRepository;
use \App\Workflows\Triggers\ITrigger;
use \App\Workflows\WorkflowHelpers;
use Carbon\Carbon;
use Illuminate\Console\Command;

class UnscheduleWorkflowJobs extends Command {
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'workflow:unschedule-jobs';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Clean up newly unneeded workflow jobs that are scheduled to be ran. Scheduled recommendation: Every 60mins";
    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $this->info("");
        $this->info("Begin unscheduling jobs");

        WorkflowRepository::activeJobs()->each(function($activeJob) {
            $trigger = WorkflowHelpers::resolveTrigger($activeJob->workflow->trigger);
            $wasUnscheduled = $trigger->unscheduleJob($activeJob);
            if($wasUnscheduled) {
                $this->line(" • Unscheduled '{$activeJob->workflow->name}' job {$activeJob->id}");
            }
        });
    }
}