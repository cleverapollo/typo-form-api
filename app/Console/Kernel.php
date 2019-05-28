<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Laravel\Lumen\Console\Kernel as ConsoleKernel;
use \App\Console\Commands\CreateNotesFromWorkflowJobs;
use \App\Console\Commands\MergeInvitationsAndUsers;
use \App\Console\Commands\ProcessWorkflowJobs;
use \App\Console\Commands\ScheduleWorkflowJobs;
use \App\Console\Commands\UnscheduleWorkflowJobs;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        CreateNotesFromWorkflowJobs::class,
        MergeInvitationsAndUsers::class,
        ProcessWorkflowJobs::class,
        ScheduleWorkflowJobs::class,
        UnscheduleWorkflowJobs::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // TODO change frequency to recommended values
        $log = storage_path('logs/workflow.log');
        $schedule->command(ScheduleWorkflowJobs::class)
            ->everyMinute()
            ->appendOutputTo($log);
        $schedule->command(ProcessWorkflowJobs::class)
            ->everyMinute()
            ->appendOutputTo($log);
        $schedule->command(UnscheduleWorkflowJobs::class)
            ->everyMinute()
            ->appendOutputTo($log);
    }
}
