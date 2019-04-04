<?php

namespace App\Services;

use App\Models\Status;
use App\Models\Response;
use App\Models\Form;
use App\Models\FormTemplate;
Use App\Models\Question;
use App\Models\QuestionType;

class FormUploadService extends Service {

    private $formService;

    public function __construct() {
        $this->formService = new FormService;
    }
    
    public function uploadFormData($data) {
        $sheet = $data['data'];
        if(!count($sheet->rows)) return;

        if($form_template = FormTemplate::with('sections.questions.answers')->where('id', $data['form_template_id'])->first()) {
            $status = Status::where('status', 'Open')->first()->id;
            $sections = $form_template->sections->pluck('id')->all();
            $questions = Question::with('answers')->whereIn('section_id', $sections)->get();
            $question_types = QuestionType::get();

            foreach($sheet->rows as $row) {
                // Check for empty row
                if(empty(array_filter($row))) continue;

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
        }
    }

    public function uploadApplicationFormData($data) {
        $sheet = $data['data'];
        if(!count($sheet->rows)) return;

        if($form_template = FormTemplate::with('sections')->where('name', $sheet->name)->first()) {
            $status = Status::where('status', 'Open')->first()->id;
            $question_types = QuestionType::get();
            $sections = $form_template->sections->pluck('id')->all();
            $questions = Question::with('answers')->whereIn('section_id', $sections)->get();

            foreach($sheet->rows as $row) {
                // Check for empty row
                if(empty(array_filter($row))) continue;

                // Get or Set Form
                if(!$form = $this->formService->findForm($form_template->id, $row)) {
                    $form = Form::create([
                        'form_template_id' => $form_template->id,
                        'user_id' => $data['user_id'],
                        'status_id' => $status
                    ]);
                }

                // Get Existing Responses
                $responses = Response::where('form_id', $form->id)->get();

                foreach($row as $key=>$val) {
                    if($question = $questions->where('key', $key)->first()) {

                        // Convert Boolean
                        if(is_bool($val)) {
                            $val = $val ? 'True' : 'False';
                        }

                        $answer = $question->answers->where('answer', $val)->first();
                        $answer_id = $answer->id ?? null;
                        $response = $answer_id ? null : $val;
                        $question_type = $question_types->where('id', $question->question_type_id)->first();                        

                        // Check for lookup or no matching answer
                        if($question_type->type === 'Lookup') {
                            $response = $this->formService->findLookupForm($question->id, $val);
                        } else {
                            if(count($question->answers) > 0 && !$answer) continue;
                        }
                        
                        // Check for change in response
                        if($responses->where('question_id', $question->id)->where('answer_id', $answer_id)->where('response', $response)->first()) continue;

                        // Update Response
                        Response::where('form_id', $form->id)->where('question_id', $question->id)->delete();
                        Response::create([
                            'form_id' => $form->id,
                            'question_id' => $question->id,
                            'response' => $response,
                            'answer_id' => $answer_id,
                            'order' => 1
                        ]);
                    }
                }
            }
        }
    }
}