<?php

namespace App\Jobs;

use App\User;
use App\Models\Role;
use App\Notifications\InformedNotification;

class SuperAdminNotification extends Job
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
	    $admin_users = $this->getSuperAdmins();
	    foreach ($admin_users as $admin_user) {
		    if ($admin_user->email) {
			    $admin_user->notify(new InformedNotification($this->config['message']));
		    }
	    }
    }

	/**
	 * Get Super Admin list
	 *
	 * @return mixed
	 */
	protected function getSuperAdmins()
	{
		return User::where('role_id', Role::where('name', 'Super Admin')->first()->id)->get();
	}
}
