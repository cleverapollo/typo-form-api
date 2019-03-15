<?php

namespace App\Services;

use App\Models\FormTemplate;
use App\Services\FileStoreService;
use Maatwebsite\Excel\Facades\Excel;

class FormTemplateService extends Service {

    private $formTemplate;
    private $fileStoreService;

    public function __construct() {
        $this->formTemplate = new FormTemplate;
        $this->fileStoreService = new FileStoreService;
    }

    public function export($form_template_id) {
        $form_template = $this->getFormTemplateRelations($form_template_id);
        $form_export_data = [];

        // Build the form template data
        foreach($form_template->sections as $section) {
            foreach($section->questions as $question) {

                $row = [];
                $row['section_name'] = $section->name; // Find By Id
                $row['parent_section_name'] = ''; // Find By Id
                $row['section_order'] = $section->order;
                $row['section_repeatable'] = $section->repeatable;
                $row['section_repeatable_rows_min_count'] = $section->min_rows;
                $row['section_repeatable_rows_max_count'] = $section->max_rows;
                $row['question'] = $question->question;
                $row['question_description'] = $question->question_description;
                $row['question_order'] = $question->order;
                $row['question_mandatory'] = $question->mandatory;
                $row['question_type'] = ''; // Find By Id
                $row['question_key'] = $question->key;
                $row['triggered_by_id'] = ''; // Find from triggers

                //$form_export_data[] = $row;     
                if($question->answers) {
                    foreach($question->answers as $answer) {
                        $answer_row = $row;
                        $answer_row['identifier'] = $answer->id;
                        $answer_row['answer'] = $answer->answer;
                        $answer_row['answer_parameter'] = $answer->answer_parameter;
                        $answer_row['answer_order'] = $answer->order;
                        $form_export_data[] = $answer_row;
                    }
                } else {
                    $form_export_data[] = $row;
                }
            }
        }

        error_log(print_r($form_export_data, 1));

        // Export the form Template Data
        $file_name = $form_template->name . '.csv';
        $file = Excel::create($file_name, function($excel) use ($form_export_data) {
            $excel->sheet('Sheet 1', function($sheet) use ($form_export_data) {
                $sheet->fromArray($form_export_data);
            });
        })->string('csv');

        return $this->fileStoreService->uploadContents($file, $file_name);
    }
}