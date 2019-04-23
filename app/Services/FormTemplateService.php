<?php

namespace App\Services;

use Exception;
use App\Models\FormTemplate;
use App\Models\Application;
use Rap2hpoutre\FastExcel\FastExcel;

class FormTemplateService extends Service {

    private $formTemplate;
    private $fileStoreService;

    public function __construct() {
        $this->formTemplate = new FormTemplate;
        $this->fileStoreService = new FileStoreService;
    }

    public function export($form_template_id) {
        // Export the form Template Data
        $form_templates = FormTemplate::where('id', $form_template_id);
        $file_name = $form_templates->first()->name . '.csv';
        try {
            $file = (new FastExcel($form_templates))->export($file_name, function ($form_template) {
                $form_export_data = [];
                // Build the form template data
                foreach ($form_template->sections as $section) {
                    foreach ($section->questions as $question) {

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
                        if ($question->answers) {
                            foreach ($question->answers as $answer) {
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
                return $form_export_data;
            });

            return $this->fileStoreService->uploadContents($file, $file_name);
        } catch (Exception $e) {
            // Send error
            return $e;
        }
    }

    /**
     * Get Application Form Templates
     *
     * @param String $application_slug
     * @return $form_templates
     */
    public function getApplicationFormTemplates(String $application_slug) {
        $form_templates = null;

        if($application = Application::where('slug', $application_slug)->first()) {
            $form_templates = FormTemplate::with(['sections.questions.answers','metas'])
                ->where('application_id', $application->id)
                ->get();
        }

        return $form_templates;
    }
}