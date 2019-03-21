<?php

namespace App\Services;

use App\Models\Status;
use App\Models\Response;
use App\Models\Form;
use App\Models\FormTemplate;
Use App\Models\Question;
use App\Models\QuestionType;
use App\Services\FormService;
use App\Services\FileStoreService;
use Maatwebsite\Excel\Facades\Excel;

class FormUploadService extends Service {

    private $formService;
    private $fileStoreService;

    public function __construct() {
        $this->formService = new FormService;
        $this->fileStoreService = new FileStoreService;
    }
    
    public function uploadFormData($data) {
        // Read the Excel file
        Excel::filter('chunk')
            ->load($data['file'])
            ->chunk(1000, function($results) use ($data) {
                // Get Form Template
                $form_template = FormTemplate::with('sections.questions.answers')->where('id', $data['form_template_id'])->first();
                $status = Status::where('status', 'Open')->first()->id;
                $sections = $form_template->sections->pluck('id')->all();
                $questions = Question::with('answers')->whereIn('section_id', $sections)->get();
                $question_types = QuestionType::get();

                foreach($results as $key=>$row) {

                    // Get or Set Form
                    if(!$form = $this->formService->findFormWhere($form_template->id, $data['where'], $row)) {
                        $form = Form::create([
                            'form_template_id' => $data['form_template_id'],
                            'user_id' => $data['user_id'],
                            'status_id' => $status
                        ]);
                    }

                    foreach($row as $key=>$val) {
                        if($question = $questions->where('key', $key)->first()) {
                            $answer = $question->answers->where('answer', $val)->first();
                            $question_type = $question_types->where('id', $question->question_type_id)->first();

                            // Check for a lookup question
                            if($question_type->type === 'Lookup') {
                                if(!$val = $this->formService->getLookupResponse($question->answers->first(), $val)) {
                                    break;
                                }
                            }
                            
                            // Delete existing response
                            Response::where('form_id', $form->id)->where('question_id', $question->id)->delete();

                            // Create the response
                            Response::create([
                                'form_id' => $form->id,
                                'question_id' => $question->id,
                                'response' => $val,
                                'answer_id' => $answer->id ?? null,
                                'order' => 1
                            ]);
                        }
                    }
                }
            });
    }

    public function uploadApplicationFormData($data) {

        error_log($data['file']);

        Excel::filter('chunk')
            ->load($data['file'])
            ->chunk(1000, function($results) use ($data) {
                error_log(var_dump($results->first()));
            });
    }
}