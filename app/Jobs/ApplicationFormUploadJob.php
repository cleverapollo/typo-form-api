<?php

namespace App\Jobs;

use App\Services\FormUploadService;

class ApplicationFormUploadJob extends Job
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
    public function handle(FormUploadService $formUploadService)
    {
        return $formUploadService->uploadApplicationFormData($this->data);
    }
}
