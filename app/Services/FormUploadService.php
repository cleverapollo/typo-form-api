<?php

namespace App\Services;

// use App\Models\Application;
use App\Models\Status;
Use App\Services\FormTemplateService;
use App\Services\FormService;
use App\Services\ResponseService;
use Maatwebsite\Excel\Facades\Excel;

class FormUploadService extends Service {

    private $formTemplateService;
    private $formService;
    private $responseService;

    public function __construct() {
        $this->formTemplateService = new FormTemplateService;
        $this->formService = new FormService;
        $this->responseService = new ResponseService;
    }
    
    public function uploadFormData($data) {
        try {
            // Get Form Template
            $form_template = $this->formTemplateService->getFormTemplateRelations($data['form_template_id']);

            // Read the Excel file
            Excel::filter('chunk')
                ->load($data['file'])
                ->chunk(10, function($results) use ($form_template, $data) {
                    $import_map = [];
                    $status = Status::where('status', 'Open')->first()->id;
                    foreach($results as $key=>$row) {

                        if($form = $this->formService->findFormWhere($form_template, $row, $data['where'])) {
                            if (!in_array($form->id, $import_map)) {
                                $this->responseService->deleteFormResponses($form->id);
                            }
                        } else {
                            $form = $this->formService->create([
                                'form_template_id' => $data['form_template_id'],
                                'user_id' => $data['user_id'],
                                'status_id' => $status
                            ]);
                        }
                        array_push($import_map, $form->id);
                        /*
                        foreach($row as $key=>$val) {
                            if($question = $this->findQuestionInSections($form_template->sections, 'key', $key)) {
                                $answer = $question->answers->where('answer', $val)->first();
                                $result = $form->responses()->create([
                                    'question_id' => $question->id,
                                    'response' => $val,
                                    'answer_id' => $answer->id ?? null,
                                    'order' => 1
                                ]);
                            }
                        }
                        */
                    }
                });

        } catch(Exception $e) {
            $this->logError($e);
        }
    }
}