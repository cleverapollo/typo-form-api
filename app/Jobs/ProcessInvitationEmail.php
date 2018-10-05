<?php

namespace App\Jobs;

use Exception;
use Illuminate\Support\Facades\Mail;

class ProcessInvitationEmail extends Job
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
    	$email = $this->config['email'];

	    // Send email to the invitee
	    Mail::send('emails.invitation', [
		    'type' => $this->config['type'],
		    'name' => $this->config['name'],
		    'link' => $this->config['link'],
	    ], function ($message) use ($email) {
		    $message->from('info@informed365.com', 'Informed 365');
			$message->to($email);
			$message->subject($this->config['title']);
	    });
    }

	/**
	 * The job failed to process.
	 *
	 * @param  Exception  $exception
	 * @return void
	 */
	public function failed(Exception $exception)
	{
		//
	}
}
