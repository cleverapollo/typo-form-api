<?php

namespace App\Services;

use App\Models\FormTemplate;
use App\Models\QuestionType;

class FormTemplateUploadService extends Service {

    private $formTemplateService;

    public function __construct() {
        $this->formTemplateService = new FormTemplateService;
    }
    
    public function uploadFormTemplateData($data) {
        $sheet = $data['data'];
        if(!count($sheet->rows)) return;

        if($form_template = FormTemplate::with('sections.questions.answers')->where('id', $data['form_template_id'])->first()) {
            // Remove Existing Data
            $form_template->sections->each(function ($section) {
                $section->delete();
            });
            $form_template->forms->each(function ($form) {
                $form->delete();
            });
            $form_template->validations->each(function ($validation) {
                $validation->delete();
            });
            $form_template->triggers->each(function ($trigger) {
                $trigger->delete();
            });

            foreach($sheet->rows as $row) {
                // Check for empty row
                if(empty(array_filter($row))) continue;

                // Get or set Parent Section
                $parent_section_id = null;
                if($row->parent_section_name) {
                    $parent_section = $form_template->sections()->where('name', $row->parent_section_name)->first();
                    if(!$parent_section) {
                        $parent_section = $form_template->sections()->create([
                            'name' => $row->parent_section_name,
                            'order' => $row->section_order ?? 1
                        ]);
                    }
                    $parent_section_id = $parent_section->id;
                }

                // Get or set Section
                $section = $form_template->sections()->where('name', $row->section_name)->where('parent_section_id', $parent_section_id)->first();
                if(!$section && $row->section_name) {
                    $section = $form_template->sections()->create([
                        'name' => $row->section_name,
                        'parent_section_id' => $parent_section_id,
                        'order' => $row->section_order ?? 1,
                        'repeatable' => $row->section_repeatable ?? 0,
                        'max_rows' => $row->section_repeatable_rows_max_count,
                        'min_rows' => $row->section_repeatable_rows_min_count
                    ]);
                }

                // Get or Set Question
                if($section && $row->question) {
                    $question = $section->questions()->where(['question' => $row->question])->first();
                    if(!$question) {
                        $question_type = $question_type = QuestionType::where('type', $row->question_type)->first();
                        $question = $section->questions()->create([
                            'question' => $row->question,
                            'description' => $row->question_description ?? '',
                            'mandatory' => $row->question_mandatory ?? 1,
                            'question_type_id' => $question_type->id ?? 1,
                            'order' => $row->question_order ?? 1
                        ]);
                    }

                    // Get or set Answer
                    if($question) {
                        $answer = $question->answers()->where('answer', $row->answer)->where('order', $row->answer_order)->first();
                        if(!$answer) {
                            $answer = $question->answers()->create([
                                'answer' => $row->answer,
                                'parameter' => $row->answer_parameter ?? true,
                                'order' => $row->answer_order ?? 1
                            ]);
                        }

                        // Add Question and Answer to answer map
                        if(isset($row->identifier)) {
                            $answer_map[$row->identifier] = ['question_id' => $question->id, 'answer_id' => $answer->id];
                        }
                    }
                }
            }

            // Loop through triggers
            $completed_triggers = [];
            foreach($sheet->rows as $row) {

                if(isset($row->triggered_by_id) && isset($row->identifier)) {
                    $triggers = explode(',', $row->triggered_by_id);

                    foreach($triggers as $key=>$trigger) {

                        if(isset($answer_map[$trigger]) && isset($answer_map[$row->identifier]) && !in_array($answer_map[$row->identifier]['question_id'], $completed_triggers)) {
                            $form_template->triggers()->create([
                                'type' => 'Question',
                                'question_id' => $answer_map[$row->identifier]['question_id'] ?? null,
                                'parent_question_id' => $answer_map[$trigger]['question_id'] ?? null,
                                'parent_answer_id' => $answer_map[$trigger]['answer_id'] ?? null,
                                'comparator_id' => 1,
                                'order' => ($key+1),
                                'operator' => 1
                            ]);
                        }
                    }
                    $completed_triggers[] = $answer_map[$row->identifier]['question_id'];
                }
            }
        }
    }
}