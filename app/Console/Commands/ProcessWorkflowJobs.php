<?php

namespace App\Console\Commands;

use \App\Repositories\WorkflowRepositoryFacade as WorkflowRepository;
use \App\Workflows\WorkflowHelpers;
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
    protected $description = "Process workflow jobs that are scheduled to be ran. Scheduled recommendation: Every 30mins";
    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $this->info("");
        $this->info("Begin processing jobs");

        $jobs = WorkflowRepository::jobsToBeProcessed();
        $this->line("Job ids to be processed: {$jobs->map->id}");

        $jobs->each(function($job) {
            // This is our last chance _not_ to push the job onto the Laravel queue. We will attempt
            // a final unschedule where the trigger has the chance to opt out. For example, an 
            // invite trigger may see the invite has been accepted, so the job isn't needed
            // anymore
            //
            $trigger = WorkflowHelpers::resolveTrigger($job->workflow->trigger);
            $wasUnscheduled = $trigger->unscheduleJob($job);
            if($wasUnscheduled) {
                return;
            }
            
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
            $actionClass = "App\\Workflows\\Actions\\{$job->workflow->action}";
            dispatch(new $actionClass($job))->onConnection('database');
            WorkflowRepository::queueJob($job);

            $this->line(" • Workflow '{$job->workflow->name}'. Queued job id: {$job->id}.");
        });
        $this->info("Completed processing jobs");
    }
}