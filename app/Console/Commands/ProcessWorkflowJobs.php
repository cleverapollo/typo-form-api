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

            // Dispatch available Workflow jobs into Queue jobs. We then mark each dispatched job as
            // queued to avoid them getting added to the Laravel Queue multiple times. 
            //
            // Future dev gotcha - there are two queues in place, the Laravel Queue, which is for 
            // offloading slow tasks, like email, and our Workflow queue (workflow_jobs table), 
            // which is for future dated actions. `WorkflowRepository::jobsToBeProcessed` will 
            // give us jobs not on the Laravel Queue by checking the status `JOB_STATUS_QUEUED`.
            // This protects us from the same job ending up on the **laravel queue**. 
            // The gotcha being: It is the reponsibility of the **trigger** (such as InviteTrigger), 
            // to determine if a Workflow job is created, because each Trigger has a unique 
            // signature
            //
            // We are not running on the default connection (in production: Redis). Redis is
            // not currently a shared cache, and so we want to ensure dispatched jobs from 
            // all app servers are sharing the same connection for this work
            //
            dispatch(new $actionClass($job))->onConnection('database');
            WorkflowRepository::queueJob($job);

            $this->line(" • Workflow '{$job->workflow->name}'. Queued job id: {$job->id}.");
        });
        $this->info("Completed processing jobs");
    }
}