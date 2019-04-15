<?php

namespace App\Jobs;

use App\Services\ApplicationService;

class ApplicationInvitationJob extends Job
{

    protected $data;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(ApplicationService $applicationService)
    {
        return $applicationService->inviteUser($this->data);
    }
}
