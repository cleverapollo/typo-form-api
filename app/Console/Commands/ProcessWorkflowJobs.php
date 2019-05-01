<?php

namespace App\Console\Commands;

use \App\Models\WorkflowJob;
use \App\Repositories\WorkflowRepositoryFacade as WorkflowRepository;
use \App\Workflows\Triggers\ITrigger;
use Carbon\Carbon;
use Illuminate\Console\Command;

class ProcessWorkflowJobs extends Command {
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'workflow:process-jobs';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Process workflow jobs that are scheduled to be ran. This command should be scheduled at a high interval.";
    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $this->createJobs();
        $this->processJobs();
    }

    // Loops over active workflows and checks if there are any valid triggers that need to be
    // queued up as a workflow job
    //
    protected function createJobs() 
    {
        $this->info("Begin creating jobs");

        WorkflowRepository::activeWorkflows()->map(function($workflow) {
            $triggerClass = "App\\Workflows\\Triggers\\{$workflow->trigger}Trigger";
            if(!class_exists($triggerClass)) {
                $this->warn("$triggerClass does not exist");
                return;
            }

            $instance = new $triggerClass();
            if(!$instance instanceof ITrigger) {
                $this->warn("$triggerClass does not implement ITrigger");
                return;
            }

            $count = $instance->scheduleJobs($workflow);
            $this->line(" • Scheduled $count '{$workflow->name}' jobs");
        });
    }

    // Loops over active workflow jobs and runs each one
    public function processJobs() 
    {
        $this->info("Begin processing jobs");

        $jobs = WorkflowRepository::jobsToBeProcessed();
        $this->line("Job ids to be processed: {$jobs->map->id}");

        $jobs->map(function($job) {
            $actionClass = "App\\Workflows\\Actions\\{$job->workflow->action}";
            dispatch(new $actionClass($job));
            $this->line(" • Workflow '{$job->workflow->name}'. Queued job id: {$job->id}.");
        });
        $this->info("Completed processing jobs");
    }
}