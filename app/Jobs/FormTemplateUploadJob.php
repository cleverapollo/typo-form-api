<?php

namespace App\Jobs;

use App\Services\FormTemplateUploadService;

class FormTemplateUploadJob extends Job
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
    public function handle(FormTemplateUploadService $formTemplateUploadService)
    {
        return $formTemplateUploadService->uploadFormTemplateData($this->data);
    }
}
