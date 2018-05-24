<?php

namespace App\Jobs;

use App\User;
use App\Models\Role;
use App\Models\ApplicationUser;
use App\Notifications\InformedNotification;

class ApplicationAdminNotification extends Job
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
	    $admin_users = $this->applicationAdmins($this->config['application_id']);
	    foreach ($admin_users as $admin_user) {
		    if ($admin_user->email) {
			    $admin_user->notify(new InformedNotification($this->config['message']));
		    }
	    }
    }

	/**
	 * Get application admins
	 *
	 * @param  $application_id
	 *
	 * @return array
	 */
	protected function applicationAdmins($application_id)
	{
		$admins = ApplicationUser::where([
			'application_id' => $application_id,
			'role_id' => Role::where('name', 'Admin')->first()->id
		])->get();

		$admin_users = [];
		foreach ($admins as $admin) {
			$admin_users[] = User::find($admin->user_id);
		}

		return $admin_users;
	}
}
