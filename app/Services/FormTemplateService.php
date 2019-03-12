<?php

namespace App\Services;

use App\Models\FormTemplate;

class FormTemplateService extends Service {

    private $formTemplate;

    public function __construct() {
        $this->formTemplate = new FormTemplate;
    }
    
    public function create($data) {
        return $this->formTemplate
            ->create($data);
    }

    public function get($id) {
        return $this->formTemplate
            ->where('id', $id)
            ->first();
    }

    public function all() {
        return $this->formTemplate
            ->get();
    }

    public function update($id, $data) {
        return $this->formTemplate
            ->where('id', $id)
            ->update($data);
    }

    public function delete($id) {
        return $this->formTemplate
            ->where('id', $id)
            ->destroy();
    }

    public function getFormTemplateRelations($id) {
        return $this->formTemplate
            ->with('sections.questions.answers')
            ->where('id', $id)
            ->first();
    }
}