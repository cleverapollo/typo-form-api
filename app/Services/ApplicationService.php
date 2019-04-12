<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Storage;
use App\Models\FormTemplate;
use App\Models\Application;
use App\Models\QuestionType;
use App\Models\Status;
use App\Models\Invitations;
use Rap2hpoutre\FastExcel\FastExcel;
use Rap2hpoutre\FastExcel\SheetCollection;

class ApplicationService extends Service {

    private $formTemplate;
    private $fileStoreService;

    public function __construct() {
        $this->formTemplate = new FormTemplate;
        $this->fileStoreService = new FileStoreService;
    }

    public function export($application_slug) {
        //Application
        ini_set('max_execution_time', 0);
        // Export the form Template Data
        $application = Application::with(['users', 'organisations', 'form_templates.forms.status', 'form_templates.sections.questions.answers', 'form_templates.sections.questions.responses'])->where('slug', $application_slug)->first();
        $file_name = $application->first()->name . '.xlsx';
        try {
            $data = [];
            $data['Applications'][$application->id] = array_map(function($item) { return is_array($item) ? null : $item; }, $application->toArray());

            //Users
            foreach($application->users as $user) {
                $user_details = array_intersect_key($user->toArray(), array_flip(['id', 'first_name', 'last_name', 'email']));
                $application_user_details = array_intersect_key($user->pivot->toArray(), array_flip(['role_id', 'created_at', 'updated_at']));
                $data['Users'][$user->id] = array_merge($user_details, $application_user_details);
            }

            //Organisations
            foreach($application->organisations as $organisation) {
                $data['Organisations'][$organisation->id] = array_map(function($item) { return is_array($item) ? null : $item; }, $organisation->toArray());
            }

            //Form Templates
            foreach($application->form_templates as $form_template) {
                $data['Form Templates'][$form_template->id] = array_map(function($item) { return is_array($item) ? null : $item; }, $form_template->toArray());

                //
                foreach($form_template->forms as $form) {
                    $data['Forms'][$form->id] = array_map(function($item) { return is_array($item) ? null : $item; }, $form->toArray());
                }

                //Sections
                foreach($form_template->sections as $section) {
                    $data['Sections'][$section->id] = array_map(function($item) { return is_array($item) ? null : $item; }, $section->toArray());

                    //Questions
                    foreach($section->questions as $question) {
                        $data['Questions'][$question->id] = array_map(function($item) { return is_array($item) ? null : $item; }, $question->toArray());

                        //Answers
                        foreach($question->answers as $answer) {
                            $data['Answers'][$answer->id] = array_map(function($item) { return is_array($item) ? null : $item; }, $answer->toArray());
                        }

                        //Responses
                        foreach($question->responses as $response) {
                            $data['Responses'][$response->id] = array_map(function($item) { return is_array($item) ? null : $item; }, $response->toArray());
                        }
                    }
                }
            }

            //Question Types
            $question_types = QuestionType::all();

            foreach($data['Form Templates'] as $form_template) {
                foreach($data['Forms'] as $form) {
                    if($form['form_template_id'] === $form_template['id']) {
                        foreach($data['Responses'] as $response) {
                            if($response['form_id'] === $form['id']) {
                                $row = [
                                    'form_template_id' => $form_template['id'],
                                    'form_template' => $form_template['name'],
                                    'form_id' => $response['form_id'],
                                    'form_created' => $form['created_at'],
                                    'form_progress' => $form['progress'],
                                    'form_status' => Status::find($form['status_id'])->status,
                                    'user_id' => $form['user_id'],
                                    'first_name' => $data['Users'][$form['user_id']]['first_name'] ?? '',
                                    'last_name' => $data['Users'][$form['user_id']]['last_name'] ?? '',
                                    'section' => $data['Sections'][$data['Questions'][$response['question_id']]['section_id']]['name'] ?? '',
                                    'question_id' => $response['question_id'],
                                    'question' => $data['Questions'][$response['question_id']]['question'] ?? '',
                                    'answer' => $data['Answers'][$response['answer_id']]['answer'] ?? '',
                                    'response_created' => $response['created_at']
                                ];

                                //Get the question type
                                $question_type_id = $data['Questions'][$response['question_id']]['question_type_id'];
                                $question_type = $question_types->firstWhere('id', $question_type_id)->type;

                                //Format the response
                                switch($question_type) {
                                    case 'Multiple choice grid':
                                        $row['response'] = $data['Answers'][$response['response']]['answer'];
                                        break;

                                    default:
                                        $row['response'] = $response['response'];
                                        break;
                                }

                                $name = substr($form_template['name'], 0, 28);
                                $data[$name][] = $row;
                            }
                        }
                    }
                }
            }

            foreach($data as $key => $value) {
                $data[$key] = collect($data[$key]);
            }

            $sheets = new SheetCollection($data);
            (new FastExcel($sheets))->export($file_name);

            $file = [];
            // $file['size'] = Storage::size($file_name);
            $file['name'] = $file_name;
            $file['url'] =  $file_name;
            $file['stored_name'] = $file_name;
            return $file;
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

    public function inviteUser ($data) {
        /**
         * TODO:
         * Insert User into invitations table
         * Send Email to User with invitation data
         */

        Invitations::create([
            'inviter_id' => $data['user_id'],
            'email' => $data['invitations']->email,
            'first_name' => $data['invitations']->firstname,
            'last_name' => $data['invitations']->lastname,
            'meta' => $data['invitations']->meta,
            'role_id' => $data['role_id']
        ]);
    }
}