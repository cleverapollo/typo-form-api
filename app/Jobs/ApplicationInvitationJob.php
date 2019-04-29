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
     * @param ApplicationService $applicationService
     *
     * @return void
     */
    public function handle(ApplicationService $applicationService)
    {
        $applicationService->inviteUser($this->data);
    }
}
