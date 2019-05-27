<?php

namespace App\Jobs;

use App\Services\OrganisationService;

class OrganisationInvitationJob extends Job
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
    public function handle(OrganisationService $organisationService)
    {
        $organisationService->inviteUser($this->data);
    }
}
