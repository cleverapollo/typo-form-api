<?php

namespace App\Services;

use App\Models\Form;
use App\Models\FormTemplate;

class FormService extends Service {

    private $form;

    public function __construct() {
        $this->form = new Form;
    }
    
    public function create($data) {
        return $this->form
            ->create($data);
    }

    public function get($id) {
        return $this->form
            ->where('id', $id)
            ->first();
    }

    public function all() {
        return $this->form
            ->get();
    }

    public function update($id, $data) {
        return $this->form
            ->where('id', $id)
            ->update($data);
    }

    public function delete($id) {
        return $this->form
            ->where('id', $id)
            ->destroy();
    }

    public function findFormWhere($form_template, $data, $where) {
        $matches = [];
        $forms = FormTemplate::with('forms.responses')->where('id', $form_template->id)->first()->forms;

        foreach($where->questions as $where) {
            if($question = $this->findQuestionInSections($form_template->sections, $where->key, $where->value)) {
                $response = $this->findResponseInForms($forms, $question->id, $data->{$where->column});
                if(!$response) {
                    return false;
                }
                $matches[] = $response->form_id;
            }
        }

        $form_id = count(array_unique($matches)) === 1 ? reset($matches) : false;
        return $this->get('id', $form_id);
    }

    public function findQuestionInSections($sections, $key, $value) {
        foreach($sections as $section) {
            if($question = $section->questions->where($key, $value)->first()) {
                return $question;
            }
        }

        return false;
    }

    public function findResponseInForms($forms, $question_id, $value) {
        foreach($forms as $form) {
            if($response = $form->responses->where('question_id', $question_id)->where('response', $value)->first()) {
                return $response;
            }
        }

        return false;
    }
}