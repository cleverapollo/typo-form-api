<?php

namespace App\Jobs;

use App\Notifications\InformedNotification;

class UsersNotification extends Job
{
	protected $config;

    /**
     * Create a new job instance.
     *
     * @param  $config
     *
     * @return void
     */
    public function __construct($config)
    {
        $this->config = $config;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
	    foreach ($this->config['users'] as $user) {
		    if ($user->email) {
			    $user->notify(new InformedNotification($this->config['message']));
		    }
	    }
    }
}
