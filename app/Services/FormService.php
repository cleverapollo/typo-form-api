<?php

namespace App\Services;

use App\Models\Form;
use App\Models\Section;
use App\Models\Question;
use App\Models\Answer;
use App\Models\QuestionType;
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

    public function findForm($form_template_id, $data) {

        if($forms = Form::where('form_template_id', $form_template_id)->get()->pluck('id')) {
            $sections = Section::where('form_template_id', $form_template_id)->get()->pluck('id');
            $questions = Question::with('answers')->whereIn('section_id', $sections)->get();
            $responses = Response::whereIn('question_id', $questions->pluck('id'))->get();
            $question_types = QuestionType::get();

            foreach($data as $key=>$val) {
                if(($question = $questions->where('key', $key)->first()) && !empty($val)) {
                    $question->type = $question_types->where('id', $question->question_type_id)->first();
                    if(($answer = $question->answers->where('answer', $val)->first()) && $question->type !== 'Lookup') {
                        $forms = $responses->whereIn('form_id', $forms)->where('question_id', $question->id)->where('answer_id', $answer->id)->pluck('form_id');
                    } else {
                        $val = $this->formatResponse($question->type, $val);
                        $forms = $responses->whereIn('form_id', $forms)->where('question_id', $question->id)->where('response', $val)->pluck('form_id');
                    }
                }

                if($forms->count() <= 1) break;
            }
        }

        $form = $forms && count($forms) === 1 ? Form::where('id', $forms->first())->first() : null;
        
        return $form;
    }

    public function findLookupForm($question_id, $value) {
        $form_id = null;
        
        if($answer = Answer::where('question_id', $question_id)->first()) {
            $link = json_decode($answer['answer']);
            if($response = Response::where('question_id', $link->questionId)->where('response', $value)->first()) {
                $form_id = $response['form_id'];
            }
        }

        return $form_id;
    }

    public function formatResponse($question, $response) {
        switch($question->type) {
            case 'Lookup':
                return $this->findLookupForm($question->id, $response);
            case 'Date':
                return $response->date ? $response->format('Y-m-d H:i:s') : null;
            default:
                return $response;
        }
    }
}