<?php

namespace App\Services;

use App\Models\Form;
use App\Models\Question;
use App\Models\FormTemplate;
use App\Models\Response;

class FormService extends Service {

    private $form;

    public function __construct() {
        $this->form = new Form;
    }

    public function findFormWhere($form_template_id, $where, $data) {
        $forms = Form::where('form_template_id', $form_template_id)->get()->pluck('id');

        foreach($where->questions as $where) {
            $question = Question::with('answers')->where($where->key, $where->value)->first();
            $answer = $question->answers->where('answer', $data->{$where->column})->first();
            $forms = Response::whereIn('form_id', $forms)->where('response', $data->{$where->column})->get()->pluck('form_id');
            if($answer) {
                $forms = $forms->merge(Response::whereIn('form_id', $forms)->where('answer_id', $answer->id)->pluck('form_id'));
            }
        
            if($forms->isEmpty() || ($forms->isNotEmpty() && $where->join === 'OR')) {
                break;
            }
        }

        $form = Form::where('id', $forms->first())->first();

        return $form;
    }

    public function getLookupResponse($answer, $val) {
        $lookup = json_decode($answer->answer);
        $response = Response::where('question_id', $lookup->questionId)->where('response', $val)->first();

        return $response->form_id ?? false;
    }
}