<?php

namespace App\Http\Controllers;

use \RoleRepository;
use \StatusRepository;
use \TypeRepository;
use Auth;
use Exception;
use Storage;
use App\User;
use App\Models\Application;
use App\Models\ApplicationUser;
use App\Models\Role;
use App\Models\FormTemplate;
use App\Models\Form;
use App\Models\Question;
use App\Models\QuestionType;
use App\Models\TriggerType;
use App\Models\Status;
use App\Services\AclFacade as Acl;
use App\Services\AclService as AclConstants;
use App\Repositories\ApplicationRepositoryFacade as ApplicationRepository;
use App\Http\Resources\SectionResource;
use App\Http\Resources\QuestionResource;
use App\Http\Resources\AnswerResource;
use App\Http\Resources\FormResource;
use App\Http\Resources\ResponseResource;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Carbon\Carbon;

class FormController extends Controller
{
	/**
	 * Create a new controller instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		$this->middleware('auth:api');
	}

	/**
	 * Display a listing of the resource.
	 *
	 * @param  int $form_template_id
	 *
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function index($form_template_id)
	{
		$user = Auth::user();

		$forms = $user->forms()->where('form_template_id', $form_template_id)->get();

		if ($user->role->name == 'Super Admin') {
            $forms = Form::where('form_template_id', $form_template_id)->get();
		}

		return $this->returnSuccessMessage('forms', FormResource::collection($forms));
	}

	/**
	 * Display a listing of the resource.
	 *
	 * @param  string $application_slug
	 *
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function all($application_slug)
	{
        $user = Auth::user();
        $application = Application::where('slug', $application_slug)->first();
        $form_templates = $application->form_templates->pluck('id');
        $forms = Form::with(['form_template', 'user', 'organisation', 'responses'])
            ->get()
            ->whereIn('form_template_id', $form_templates);

        // Filter forms by user and organisation
        if(!$this->hasPermission($user, $application->id)) {
            $organisations = $user->organisations->where('application_id', $application->id)->pluck('id');
            $forms = $forms->filter(function ($form) use ($user, $organisations) {
                return $form->user_id === $user->id || $organisations->contains($form->organisation_id);
            });
        }
        
        return response()->json(['forms' => FormResource::collection($forms)]);
	}

    /**
     * Display a listing of the resource.
     *
     * @param  string $application_slug
     * @param  integer $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function one($application_slug, $id)
    {
        $user = Auth::user();
        $application = ApplicationRepository::bySlug($user, $application_slug);
        $form = Form::findOrfail($id);

        $this->verifyFormTemplate($form->form_template, $application_slug);
        Acl::authorize($user, $application, AclConstants::SHOW, $form->form_template);

        return $this->returnSuccessMessage('form', new FormResource(Form::with(['form_template', 'responses'])->find($form->id)));
	}

    /*
    public function uploadFormDataByColumn($form_template, $results, $where) {
        foreach ($results as $row) {

            //Find Existing Organisation
            $organisation = $application->organisations()->where('name', $row->organisation)->first();
            if(!$organisation && $row->organisation) {

                // Create Organisation
                $share_token = base64_encode(str_random(40));
                while (!is_null(Organisation::where('share_token', $share_token)->first())) {
                    $share_token = base64_encode(str_random(40));
                }
                $organisation = $application->organisations()->create([
                    'name' => $row->organisation,
                    'share_token' => $share_token
                ]);
            }

            // Find Existing Form or Create
            // Get form using where query
            $form = null;
            if($where) {
                $form = $this->findFormWhere($form_template, $row, $where);
            } elseif($organisation) {
                $form = $form_template->forms()->where('organisation_id', $organisation->id)->first();
            }

            if ($form && !in_array($form->id, $import_map)) {
                // Remove Existing Data
                $form->responses->each(function ($response) {
                    $response->delete();
                });
                array_push($import_map, $form->id);
            }
            if(!$form) {
                // Create Form
                $form = $form_template->forms()->create([
                    'user_id' => $user->id,
                    'organisation_id' => $organisation ? $organisation->id : null,
                    'progress' => 0,
                    'status_id' => Status::where('status', 'Open')->first()->id
                ]);
                array_push($import_map, $form->id);
            }
            
            // Get Parent Section
            $parent_section = $form_template->sections()->where(['name' => $row->parent_section])->first();
            $parent_section_id = $parent_section->id ?? null;

            // Get Section
            $section = $form_template->sections()->where(['name' => $row->section])->where('parent_section_id', $parent_section_id)->first();
            if($section) {
                // Get Question
                $question = $section->questions()->where(['question' => $row->question])->first();
                if($question) {
                    // Get Answers
                    $answer = $question->answers()->where(['answer' => $row->answer])->first();
                    $response = (!empty($row->response) && !is_null($row->response) && $row->response !== "NULL") ? $row->response : null;

                    // Set Response
                    if($answer || $response) {
                        $form->responses()->create([
                            'question_id' => $question->id,
                            'response' => $response,
                            'answer_id' => ($answer) ? $answer->id : null,
                            'order' => empty($row->order) ? 1 : $row->order
                        ]);
                    }
                }
            }

            // Set Form Status and Progress
            $status_id = Status::where('status', ($row->status ?? 'Closed'))->first()->id;
            $form->update([
                'progress' => $row->status && $row->status === 'Closed' ? 100 : $row->progress ?? 0,
                'status_id' => $status_id
            ]);
        }        
    }
	
	public function uploadFormData($application_slug, $id, Request $request) {
		try {
            switch($request->input('method')) {
                case 'key': 
                    return $this->uploadFormDataByKey($application_slug, $id, $request);
                default: 
                    return $this->uploadFormDataByColumn($application_slud, $id, $request);
            }
		} catch (Exception $e) {
			return $this->returnErrorMessage(503, $e->getMessage() . ' on line ' . $e->getLine());
		}
    }


    public function uploadFormDataByKey($application_slug, $id, Request $request) {
        try {
			// Get Application
			$user = Auth::user();
			if(!$application = $this->getApplication($user, $application_slug)) {
				return $this->returnApplicationNameError();
			}

			// Check User Permissions
			if(!$this->isUserApplicationAdmin($user, $application)) {
				return $this->returnError('application', 403, 'update form data');
			}

            // Get Form Template
            if(!$form_template = $application->form_templates()->with('sections.questions.answers')->where('id', $id)->first()) {
				return $this->returnError('form_template', 404, 'update form data');
            }

			// Check Valid File
			if(!$request->file('file')->isValid()) {
				return $this->returnErrorMessage(503, 'Invalid CSV file.');
			}

            // Get Params
            $where = json_decode($request->input('where', null));
            $path = $request->file('file')->getRealPath();

            ini_set('max_execution_time', 0);
            Excel::filter('chunk')->load($path)->chunk(, function($results) use ($form_template, $where, $user) {
                $import_map = [];
                foreach($results as $key=>$row) {
                    error_log($key);

                    // Find Existing Form or Create
                    if($form_id = $this->findFormWhere($form_template, $row, $where)) {
                        $form = Form::where('id', $form_id)->first();

                        // Delete Existing Responses
                        if (!in_array($form->id, $import_map)) {
                            $form->responses->each(function ($response) {
                                $response->delete();
                            });
                        }
                    } else {
                        // Create Form
                        $form = $form_template->forms()->create([
                            'user_id' => $user->id,
                            'progress' => 0,
                            'status_id' => Status::where('status', 'Open')->first()->id
                        ]);
                    }
                    array_push($import_map, $form->id);
        
                    foreach($row as $key=>$val) {
                        if($question = $this->findQuestionInSections($form_template->sections, 'key', $key)) {
                            $answer = $question->answers->where('answer', $val)->first();
                            $result = $form->responses()->create([
                                'question_id' => $question->id,
                                'response' => $val,
                                'answer_id' => $answer->id ?? null,
                                'order' => 1
                            ]);
                        }
                    }
                }
            });

            return $this->returnSuccessMessage('upload', 'Form data successfully uploaded.');
        } catch (Exception $e) {
            error_log($e->getMessage() . ' on line ' . $e->getLine());
            return $this->returnErrorMessage(503, $e->getMessage() . ' on line ' . $e->getLine());
        }
    }

    private function findFormWhere($form_template, $data, $where) {
        $matches = [];
        $forms = FormTemplate::with('forms.responses')->where('id', $form_template->id)->first()->forms;

        // Questions
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
        return $form_id;
    }

    private function findQuestionInSections($sections, $key, $value) {
        foreach($sections as $section) {
            if($question = $section->questions->where($key, $value)->first()) {
                return $question;
            }
        }

        return false;
    }

    private function findResponseInForms($forms, $question_id, $value) {
        foreach($forms as $form) {
            if($response = $form->responses->where('question_id', $question_id)->where('response', $value)->first()) {
                return $response;
            }
        }

        return false;
    }
    */

    /**
     * Result of Trigger
     * @param object $responses
     * @param object $trigger
     * @param integer $order
     * @return bool
     */
    public function check_trigger($responses, $trigger, $order) {
        $question = Question::find($trigger->parent_question_id);
        $parent_responses = $responses->where('question_id', $trigger->parent_question_id)->where('order', $order)->toArray();
        $trigger_type = TriggerType::where([
            'question_type_id' => $question->question_type_id,
            'comparator_id' => $trigger->comparator_id
        ])->first();
        if (!$trigger_type) {
            return true;
        }
        $question_type = QuestionType::find($question->question_type_id);
        $comparator = $trigger->comparator;
        $answer_f = $trigger_type->answer;
        $value_f = $trigger_type->value;
        $answer = $trigger->parent_answer_id;
        $value = $trigger->value;

        $question_answer = '';
        $question_value = '';
        if (count($parent_responses)) {
            if ($question_type->type === 'Checkboxes' || $question_type->type === 'Dropdown') {
                $filtered_responses = array_filter($parent_responses, function($value) use ($answer) {
                    return $value->answer_id == $answer;
                });
                if (count($filtered_responses)) {
                    $question_answer = (string)$answer;
                }
            } else if ($question_type->type === 'Checkbox grid' || $question_type->type === 'Multiple choice grid') {
                $filtered_responses = array_filter($parent_responses, function($value) use ($answer) {
                    return $value->answer_id == $answer && $value->response == $value;
                });
                if (count($filtered_responses)) {
                    $question_answer = (string)$answer;
                    $question_value = $value;
                }
            } else {
                if (array_values($parent_responses)[0]['answer_id']) {
                    $question_answer = (string)array_values($parent_responses)[0]['answer_id'];
                }
                $question_value = array_values($parent_responses)[0]['response'];
            }
        }
        $answer = $answer ? (string)$answer : null;
        $values = $value ? explode(",", $value) : [];
        $list_value = array_map(function ($value) {
            return $value['response'];
        }, $parent_responses);
        if ($question_type->type === 'Checkboxes' || $question_type->type === 'Dropdown' || $question_type->type === 'Multiple choice') {
            $list_value = array_map(function ($value) use ($question) {
                return $question->answers->find('id', $value->answer_id)->answer;
            }, $parent_responses);
        }
        switch ($comparator->comparator) {
            case 'equals':
                if (!$answer_f) {
                    if ($question_value === $value) {
                        return true;
                    }
                } else {
                    if (!$value_f) {
                        return $question_answer === $answer;
                    } else {
                        return $question_answer === $answer && $question_value === $value;
                    }
                }
                break;
            case 'not equal to':
                if (!$answer_f) {
                    if ($question_value !== $value) {
                        return true;
                    }
                } else {
                    if (!$value_f) {
                        return $question_answer !== $answer;
                    } else {
                        return $question_answer === $answer && $question_value !== $value;
                    }
                }
                break;
            case 'less than':
                if (!$answer_f) {
                    if ($question_value < $value) {
                        return true;
                    }
                } else {
                    if ($question_answer < $answer) {
                        return true;
                    }
                }
                break;
            case 'greater than':
                if (!$answer_f) {
                    if ($question_value > $value) {
                        return true;
                    }
                } else {
                    if ($question_answer > $answer) {
                        return true;
                    }
                }
                break;
            case 'less than or equal to':
                if (!$answer_f) {
                    if ($question_value <= $value) {
                        return true;
                    }
                } else {
                    if ($question_answer <= $answer) {
                        return true;
                    }
                }
                break;
            case 'greater than or equal to':
                if (!$answer_f) {
                    if ($question_value >= $value) {
                        return true;
                    }
                } else {
                    if ($question_answer >= $answer) {
                        return true;
                    }
                }
                break;
            case 'contains':
                if ($question_type->type === 'Dropdown') {
                    return count(array_filter($list_value, function ($element) use ($value) {
                        return strpos($element, $value) !== false;
                    }));
                } else if ($question_type->type ===  'Checkboxes') {
                    return $question_answer === $answer;
                } else if ($question_type->type ===  'Checkbox grid' || $question_type->type ===  'Multiple choice grid') {
                    return $question_answer === $answer && $question_value === $value;
                } else {
                    if (!$answer_f) {
                        return strpos($question_value, $value) !== false;
                    } else if (!$value_f) {
                        return strpos($question_answer, $answer) !== false;
                    } else {
                        return strpos($question_answer, $answer) !== false && strpos($question_value, $value) !== false;
                    }
                }
            case 'not contains':
                if ($question_type->type === 'Dropdown') {
                    return count(array_filter($list_value, function ($element) use ($value) {
                            return strpos($element, $value) !== false;
                        })) == 0;
                } else if ($question_type->type ===  'Checkboxes') {
                    return $question_answer !== $answer;
                } else if ($question_type->type ===  'Checkbox grid' || $question_type->type ===  'Multiple choice grid') {
                    return $question_answer !== $answer || $question_value !== $value;
                } else {
                    if (!$answer_f) {
                        return strpos($question_value, $value) === false;
                    } else if (!$value_f) {
                        return strpos($question_answer, $answer) === false;
                    } else {
                        return strpos($question_answer, $answer) === false || strpos($question_value, $value) === false;
                    }
                }
            case 'starts with':
                if (!$answer_f) {
                    return substr($question_value, 0, strlen($value)) === $value;
                } else {
                    return substr($question_answer, 0, strlen($answer)) === $answer;
                }
            case 'ends with':
                if (!$answer_f) {
                    return substr($question_value, -strlen($value)) === $value;
                } else {
                    return substr($question_answer, -strlen($answer)) === $answer;
                }
            case 'is null':
                return !count($parent_responses);
            case 'is not null':
                return !count($parent_responses);
            case 'in list':
                return count(array_filter($list_value, function ($value) use ($values) {
                    return in_array($value, $values);
                }));
            case 'not in list':
                return count(array_filter($list_value, function ($value) use ($values) {
                        return in_array($value, $values);
                    })) == 0;
            case 'is invalid':
                if (count($parent_responses)) {
                    return $question_value === '';
                } else {
                    return false;
                }
            default:
                break;
        }
        return false;
    }

    /**
     * Result of Array Trigger
     *
     * @param object $form_content
     * @param array $triggers
     * @param integer $order
     * @param object $responses
     * @return bool
     */
    public function check_triggers($form_content, $triggers, $order, $responses) {
        // if length = 0, show is true
        if (!count($triggers)) {
            return true;
        }

        // Result of Triggers
        $tempF = true;
        foreach($triggers as $trigger) {
            $parent_question = Question::find($trigger->parent_question_id);
            $tempF = $tempF && $this->check_trigger($responses, $trigger, $order) && $form_content->sections->{$parent_question->section_id}->orders->{$order}->questions->{$parent_question->id}->show;
            if ($trigger->operator === 1 || $trigger->operator === true) {
                if ($tempF) {
                    return true;
                }
                $tempF = true;
            }
        }
        return $tempF;
    }

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request
     * @return float progress
     */
    public function progress($form)
    {
        // Form
        $form_template = $form->form_template;
        $form_content = clone $form_template;

        // Sections
        $form_content->sections = (object) null;
        foreach ($form_template->sections as $section) {
            // Section
            $form_content->sections->{$section['id']} = clone $section;
            $section_content = $form_content->sections->{$section['id']};
            $section_content->show = true;
            $orders = [1];

            foreach ($section->questions as $question) {
                foreach ($question->responses as $response) {
                    array_push($orders, $response->order);
                }
            }
            $orders = array_unique($orders);

            // Orders
            $section_content->orders = (object) null;
            foreach ($orders as $order) {
                // Order
                $section_content->orders->{$order} = (object) null;
                $ordered_section = $section_content->orders->{$order};

                // Questions
                $ordered_section->questions = (object) null;
                foreach ($section->questions as $question) {
                    // Question
                    $ordered_section->questions->{$question['id']} = clone $question;
                    $question_content = $ordered_section->questions->{$question['id']};
                    $question_content->show = true;
                    $question_content->responses = (object) null;

                    // Answers
                    $question_content->answers = (object) null;
                    foreach ($question->answers as $answer) {
                        // Answer
                        $question_content->answers->{$answer['id']} = clone $answer;
                    }

                    // Question Triggers
                    $question_content->triggers = (object) null;
                    foreach ($form_template->triggers as $trigger) {
                        // Question Trigger
                        if ($trigger->type == 'Question' && $trigger->question_id == $question->id) {
                            $question_content->triggers->{$trigger['id']} = $trigger;
                        }
                    }

                    // Validations
                    $question_content->validations = (object) null;
                    foreach ($question->validations as $validation) {
                        // Validation
                        $question_content->validations->{$validation['id']} = $validation;
                    }
                }
            }

            // Section Triggers
            $section_content->triggers = (object) null;
            foreach ($form_template->triggers as $trigger) {
                if ($trigger->type == 'Section' && $trigger->question_id == $section->id) {
                    // Section Trigger
                    $section_content->triggers->{$trigger['id']} = $trigger;
                }
            }
        }

        // Responses
        $response_resources = ResponseResource::collection(clone $form->responses);
        foreach ($response_resources as $response_resource) {
            $question = Question::find($response_resource->question_id);
            $form_content->sections->{$question->section_id}->orders->{$response_resource->order}->questions->{$question->id}->responses->{$response_resource->id} = $response_resource;
        }

        // Form Level of Submission Content
        $form_content->number_of_questions = 0;
        $form_content->number_of_mandatory_questions = 0;
        $form_content->number_of_questions_answered = 0;
        $form_content->number_of_mandatory_questions_answered = 0;
        $form_content->progress = 0;
        // Parent Sections
        $parent_sections = $form_template->sections->where('parent_section_id', null)->sortBy('order');
        foreach ($parent_sections as $parent_section) {
            // Parent Section
            $form_content->sections->{$parent_section->id}->number_of_questions = 0;
            $form_content->sections->{$parent_section->id}->number_of_mandatory_questions = 0;
            $form_content->sections->{$parent_section->id}->number_of_questions_answered = 0;
            $form_content->sections->{$parent_section->id}->number_of_mandatory_questions_answered = 0;
            $form_content->sections->{$parent_section->id}->progress = 0;
            $child_sections = $form_template->sections->where('parent_section_id', $parent_section->id)->sortBy('order');

            // if Parent Section is null, show is false
            if (!count($child_sections) && !count($parent_section->questions)) {
                $form_content->sections->{$parent_section->id}->show = false;
                continue;
            }

            // Parent Section Triggers
            $triggers = $form_template->triggers->where('type', 'Section')->where('question_id', $parent_section->id)->sortBy('order');
            $trigger_result = $this->check_triggers($form_content, $triggers, 1, $form->responses);
            // if Result is true, show is false
            if ($trigger_result == false) {
                $form_content->sections->{$parent_section->id}->show = false;
                continue;
            }

            // Parent Section Question Trigger
            $orders = [1];
            foreach ($parent_section->questions as $question) {
                foreach ($question->responses as $response) {
                    array_push($orders, $response->order);
                }
            }
            $orders = array_unique($orders);

            $questions = $parent_section->questions->sortBy('order');
            $flag = true;
            if (!count($child_sections)) {
                $flag = false;
            }
            foreach ($orders as $order) {
                foreach ($questions as $question) {
                    $triggers = $form_template->triggers->where('type', 'Question')->where('question_id', $question->id)->sortBy('order');
                    $trigger_result = $this->check_triggers($form_content, $triggers, $order, $form->responses);
                    $form_content->sections->{$parent_section->id}->orders->{$order}->questions->{$question->id}->show = $trigger_result;
                    $flag = $flag || $trigger_result;
                    if ($trigger_result) {
                        $form_content->sections->{$parent_section->id}->number_of_questions++;
                        if ($question->mandatory) {
                            $form_content->sections->{$parent_section->id}->number_of_mandatory_questions++;
                        }
                        if (count($question->responses)) {
                            $form_content->sections->{$parent_section->id}->number_of_questions_answered++;
                            if ($question->mandatory) {
                                $form_content->sections->{$parent_section->id}->number_of_mandatory_questions_answered++;
                            }
                        }
                    }
                }
            }
            $form_content->sections->{$parent_section->id}->show = $flag;

            // Children Section Trigger
            foreach ($child_sections as $child_section) {
                // Child Section
                $form_content->sections->{$child_section->id}->number_of_questions = 0;
                $form_content->sections->{$child_section->id}->number_of_mandatory_questions = 0;
                $form_content->sections->{$child_section->id}->number_of_questions_answered = 0;
                $form_content->sections->{$child_section->id}->number_of_mandatory_questions_answered = 0;
                $form_content->sections->{$child_section->id}->progress = 0;

                // if Parent Section is null, show is false
                if (!count($child_section->questions)) {
                    $form_content->sections->{$child_section->id}->show = false;
                    continue;
                }

                // Child Section Triggers
                $triggers = $form_template->triggers->where('type', 'Section')->where('question_id', $child_section->id)->sortBy('order');
                $trigger_result = $this->check_triggers($form_content, $triggers, 1, $form->responses);
                // if Result is true, show is false
                if ($trigger_result == false) {
                    $form_content->sections->{$child_section->id}->show = false;
                    continue;
                }

                // Child Section Question Trigger
                $orders = [1];
                foreach ($child_section->questions as $question) {
                    foreach ($question->responses as $response) {
                        array_push($orders, $response->order);
                    }
                }
                $orders = array_unique($orders);

                $questions = $child_section->questions->sortBy('order');
                $flag = false;
                foreach ($orders as $order) {
                    foreach ($questions as $question) {
                        $triggers = $form_template->triggers->where('type', 'Question')->where('question_id', $question->id)->sortBy('order');
                        $trigger_result = $this->check_triggers($form_content, $triggers, $order, $form->responses);
                        $form_content->sections->{$child_section->id}->orders->{$order}->questions->{$question->id}->show = $trigger_result;
                        $flag = $flag || $trigger_result;
                        if ($trigger_result) {
                            $form_content->sections->{$child_section->id}->number_of_questions++;
                            if ($question->mandatory) {
                                $form_content->sections->{$child_section->id}->number_of_mandatory_questions++;
                            }
                            if (count($question->responses)) {
                                $form_content->sections->{$child_section->id}->number_of_questions_answered++;
                                if ($question->mandatory) {
                                    $form_content->sections->{$child_section->id}->number_of_mandatory_questions_answered++;
                                }
                            }
                        }
                    }
                }
                if ($form_content->sections->{$child_section->id}->number_of_mandatory_questions) {
                    $form_content->sections->{$child_section->id}->progress = (100 * $form_content->sections->{$child_section->id}->number_of_mandatory_questions_answered / $form_content->sections->{$child_section->id}->number_of_mandatory_questions);
                }
                $form_content->sections->{$child_section->id}->show = $flag;
                $form_content->sections->{$parent_section->id}->number_of_questions += $form_content->sections->{$child_section->id}->number_of_questions;
                $form_content->sections->{$parent_section->id}->number_of_mandatory_questions += $form_content->sections->{$child_section->id}->number_of_mandatory_questions;
                $form_content->sections->{$parent_section->id}->number_of_questions_answered += $form_content->sections->{$child_section->id}->number_of_questions_answered;
                $form_content->sections->{$parent_section->id}->number_of_mandatory_questions_answered += $form_content->sections->{$child_section->id}->number_of_mandatory_questions_answered;
            }
            if ($form_content->sections->{$parent_section->id}->number_of_mandatory_questions) {
                $form_content->sections->{$parent_section->id}->progress = (100 * $form_content->sections->{$parent_section->id}->number_of_mandatory_questions_answered / $form_content->sections->{$parent_section->id}->number_of_mandatory_questions);
            }
            $form_content->number_of_questions += $form_content->sections->{$parent_section->id}->number_of_questions;
            $form_content->number_of_mandatory_questions += $form_content->sections->{$parent_section->id}->number_of_mandatory_questions;
            $form_content->number_of_questions_answered += $form_content->sections->{$parent_section->id}->number_of_questions_answered;
            $form_content->number_of_mandatory_questions_answered += $form_content->sections->{$parent_section->id}->number_of_mandatory_questions_answered;
        }

        if ($form_content->number_of_mandatory_questions) {
            $form_content->progress = (100 * $form_content->number_of_mandatory_questions_answered / $form_content->number_of_mandatory_questions);
        }
        return $form_content->progress;
    }

	/**
	 * Store a newly created resource in storage.
	 *
	 * @param  int $form_template_id
	 * @param  \Illuminate\Http\Request $request
	 *
	 * @return \Illuminate\Http\JsonResponse
	 * @throws \Illuminate\Validation\ValidationException
	 */
	public function store($form_template_id, Request $request)
	{
        $form_template = FormTemplate::findOrFail($form_template_id);

        $isOrganisationForm = TypeRepository::idByName('organisation') === $form_template->type_id;
        $isSelfSubmitting = !$request->has('user_id');

        if($isOrganisationForm) {
            $rules = ['organisation' => 'required|string|filled'];
        } else if($isSelfSubmitting) {
            $rules = [];
        } else {
            $rules = ['user_id' => 'required|integer|exists:users,id'];
        }

        $this->validate($request, $rules);

        $user = Auth::user();
        $user_id = $isSelfSubmitting ? $user->id : $request->input('user_id');
        
        // Check whether user has permission
        if ($form_template->status->status == 'Open' && !$this->hasPermission($user, $form_template->application_id)) {
            return $this->returnError('application', 403, 'create form');
        }

        if($isOrganisationForm) {
            $organisation = $user->organisations()->firstOrCreate([
                'name' => $request->input('organisation'),
                'application_id' => $form_template->application_id,
            ], [
                'role_id' => RoleRepository::idByName('User'),
            ]);
        }

        $form = $form_template->forms()->create([
            'user_id' => $user_id,
            'organisation_id' => $organisation->id ?? null,
            'progress' => $request->input('progress', 0),
            'period_start' => $request->input('period_start', null),
            'period_end' => $request->input('period_end', null),
            'status_id' => StatusRepository::idByName('Open'),
        ]);

        return $this->returnSuccessMessage('form', new FormResource(Form::find($form->id)));
	}

    /**
     * Duplicate a resource in storage.
     *
     * @param  int $form_template_id
     * @param  int $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function duplicate($form_template_id, $id)
    {
        try {
            $form_template = FormTemplate::find($form_template_id);

            // Send error if section does not exist
            if (!$form_template) {
                return $this->returnError('form template', 404, 'create form');
            }

            // Check whether user has permission
            $user = Auth::user();
            if ($form_template->status->status == 'Open' && !$this->hasPermission($user, $form_template->application_id)) {
                return $this->returnError('application', 403, 'create form');
            }

            $form = $form_template->forms()->find($id);

            // Send error if question does not exist
            if (!$form) {
                return $this->returnError('form', 404, 'duplicate');
            }

            // Duplicate form
            $newForm = $form_template->forms()->create([
                'user_id' => $form->user_id,
                'organisation_id' => $form->organisation_id,
                'progress' => $form->progress,
                'period_start' => $form->period_start,
                'period_end' => $form->period_end,
                'status_id' => $form->status_id
            ]);

            if ($newForm) {
                // Duplicate children responses
                $form->responses()->get()->each(function ($response) use ($newForm) {
                    $newForm->responses()->create([
                        'question_id' => $response->question_id,
                        'response' => $response->response,
                        'answer_id' => $response->answer_id,
                        'order' => $response->order
                    ]);
                });

                return $this->returnSuccessMessage('form', new FormResource(Form::find($newForm->id)));
            }

            // Send error if question is not created
            return $this->returnError('form', 503, 'duplicate');
        } catch (Exception $e) {
            // Send error
            return $this->returnErrorMessage(503, $e->getMessage());
        }
    }

	/**
	 * Display the specified resource.
	 *
	 * @param  int $form_template_id
	 * @param  int $id
	 *
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function show($form_template_id, $id)
	{
		$form_template = FormTemplate::find($form_template_id);

		// Send error if form template does not exist
		if (!$form_template) {
			return $this->returnError('form template', 404, 'show form');
		}

		$form = Form::find($id);

		if ($form) {
			$user = Auth::user();
			if ($this->hasPermission($user, $form_template->application_id) || $form->user_id != $user->id) {
				return $this->returnError('form', 403, 'see');
			}

			return $this->returnSuccessMessage('form', new FormResource($form));
		}

		// Send error if form does not exist
		return $this->returnError('form', 404, 'show');
	}

	/**
	 * Display the specified resource.
	 *
	 * @param  int $form_template_id
	 * @param  int $id
	 *
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function getData($form_template_id, $id)
	{
		$form_template = FormTemplate::find($form_template_id);

		// Send error if form template does not exist
		if (!$form_template) {
			return $this->returnError('form template', 404, 'show form');
		}

        $form = $form_template->forms()->find($id);
		if ($form) {
			$user = Auth::user();
			if ($this->hasPermission($user, $form_template->application_id) || $form->user_id != $user->id) {
				return $this->returnError('form', 403, 'see');
			}

			$data = SectionResource::collection($form_template->sections()->get());
			foreach ($data as $section) {
				$questions = QuestionResource::collection($section->questions()->get());
				foreach ($questions as $question) {
					$answers = AnswerResource::collection($question->answers()->get());
					foreach ($answers as $answer) {
						$answer['responses'] = ResponseResource::collection($answer->responses()->where([
							['form_id', '=', $id],
							['question_id', '=', $question->id]
						]));
					}
				}
			}

			return $this->returnSuccessMessage('data', $data);
		}

		// Send error if form does not exist
		return $this->returnError('form', 404, 'show');
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  int $form_template_id
	 * @param  int $id
	 * @param  \Illuminate\Http\Request $request
	 *
	 * @return \Illuminate\Http\JsonResponse
	 * @throws \Illuminate\Validation\ValidationException
	 */
	public function update($form_template_id, $id, Request $request)
	{
		$this->validate($request, [
			'user_id' => 'nullable|integer|min:1',
			'organisation_id' => 'nullable|integer|min:1',
			'progress' => 'filled|integer|min:0',
			'period_start' => 'nullable|date',
			'period_end' => 'nullable|date',
			'status_id' => 'filled|integer|min:1'
		]);

		try {
			$form_template = FormTemplate::find($form_template_id);

			// Send error if form template does not exist
			if (!$form_template) {
				return $this->returnError('form template', 404, 'update form');
			}

			$user = Auth::user();
			
			$form = $form_template->forms()->where([
				'id' => $id,
				'user_id' => $user->id
			])->first();

            // Check whether user has permission
            if ($form_template->status->status == 'Open' && !$this->hasPermission($user, $form_template->application_id)) {
                return $this->returnError('application', 403, 'update form');
            }

			if (!$form && $this->hasPermission($user, $form_template->application_id)) {
                $form = $form_template->forms()->where([
					'id' => $id
				])->first();
			}

			// Send error if form does not exist
			if (!$form) {
				return $this->returnError('form', 404, 'update');
			}

			// Check whether the question type exists or not
			$status_id = $request->input('status_id', null);
			if ($status_id && !Status::find($status_id)) {
				return $this->returnError('status', 404, 'update form');
			}

			$new_status = Status::find($status_id);

            if ($status_id && $form->status->status == 'Open' && $new_status->status == 'Closed') {
                $submitted_date = Carbon::now();
                $form->update(['submitted_date' => $submitted_date]);
            }

			// Update form
			if ($form->fill($request->only('user_id', 'organisation_id', 'progress', 'period_start', 'period_end', 'status_id'))->save()) {
				$form->touch();

				return $this->returnSuccessMessage('form', new FormResource(Form::find($form->id)));
			}

			// Send error if there is an error on update
			return $this->returnError('form', 503, 'update');
		} catch (Exception $e) {
			// Send error
			return $this->returnErrorMessage(503, $e->getMessage());
		}
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int $form_template_id
	 * @param  int $id
	 *
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function destroy($form_template_id, $id)
	{
		try {
			$form_template = FormTemplate::find($form_template_id);
			$user = Auth::user();

			// Send error if form does not exist
			if (!$form_template) {
				return $this->returnError('form_template', 404, 'delete form');
			}

			$form = $form_template->forms()->where([
				'id' => $id,
				'user_id' => $user->id
			])->first();

			if ($this->hasPermission($user, $form_template->application_id)) {
				$form = $form_template->forms()->where([
					'id' => $id
				])->first();
			}

			// Send error if form does not exist
			if (!$form) {
				return $this->returnError('form', 404, 'delete');
			}

			if ($form->delete()) {
				return $this->returnSuccessMessage('message', 'Form has been deleted successfully.');
			}

			// Send error if there is an error on delete
			return $this->returnError('form', 503, 'delete');
		} catch (Exception $e) {
			// Send error
			return $this->returnErrorMessage(503, $e->getMessage());
		}
    }
    
    protected function verifyFormTemplate($formTemplate, $applicationSlug)
    {
        if (!$formTemplate) {
            throw (new ModelNotFoundException)->setModel('form_template');
        } elseif ($formTemplate->application->slug !== $applicationSlug) {
            throw (new ModelNotFoundException)->setModel('application');
        }
    }

	/**
	 * Check whether user has permission or not
	 *
	 * @param  $user
	 * @param  $application_id
	 *
	 * @return bool
	 */
	protected function hasPermission($user, $application_id)
	{
		if ($user->role->name == 'Super Admin') {
			return true;
		}

		$role = ApplicationUser::where([
			'user_id' => $user->id,
			'application_id' => $application_id
		])->first()->role;

		if ($role->name != 'Admin') {
			return false;
		}

		return true;
	}	
}
