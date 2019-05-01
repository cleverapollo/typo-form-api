<?php

namespace App\Console\Commands;

use \App\Repositories\WorkflowRepositoryFacade as WorkflowRepository;
use \App\Workflows\Triggers\ITrigger;
use \App\Workflows\WorkflowHelpers;
use Illuminate\Console\Command;

class ScheduleWorkflowJobs extends Command {
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'workflow:schedule-jobs';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Create workflow jobs based on trigger criteria. Scheduled recommendation: Every 5mins";
    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        // Loops over active workflows and checks if there are any valid triggers that need to be
        // queued up as a workflow job
        //
        $this->info("");
        $this->info("Begin scheduling jobs");

        WorkflowRepository::activeWorkflows()->each(function($workflow) {
            // TODO passing warn() is silly/lazy ... Throw/catch
            $instance = WorkflowHelpers::resolveTrigger($workflow->trigger, [$this, 'warn']);
            if($instance) {
                $count = $instance->scheduleJobs($workflow);
                $this->line(" • Scheduled $count '{$workflow->name}' jobs");
            }
        });
    }
}