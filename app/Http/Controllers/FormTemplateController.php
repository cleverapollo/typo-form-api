<?php

namespace App\Http\Controllers;

use Auth;
use Exception;
use App\Models\AccessLevel;
use App\Models\Application;
use App\Models\ApplicationUser;
use App\Models\FormTemplate;
use App\Models\Type;
use App\Models\Status;
use App\Http\Resources\FormTemplateResource;
use App\Repositories\ApplicationRepositoryFacade as ApplicationRepository;
use Illuminate\Http\Request;

use App\Services\FormTemplateService;

class FormTemplateController extends Controller
{

	private $formTemplateService;

	/**
	 * Create a new controller instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		//$this->middleware('auth:api', ['except' => []]);
		$this->middleware('auth:api');
		$this->formTemplateService = new FormTemplateService;
	}

	/**
	 * Display a listing of the resource.
	 *
	 * @param  string $application_slug
	 *
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function index($application_slug)
	{
		$form_templates = $this->formTemplateService->getApplicationFormTemplates($application_slug);
		return $this->jsonResponse(['form_templates' => $form_templates]);

	/*	$user = Auth::user();

		if($user->role->name === 'Super Admin') {
			$application = Application::with('form_templates.metas')->where('slug', $application_slug)->first();
		} else {
			$user->load('applications.form_templates.metas');
			$application = $user->applications()->where('slug', $application_slug)->first();
		}

		// No Application
		if (!$application) {
			return $this->returnApplicationNameError();
		}

		return $this->returnSuccessMessage('form_templates', FormTemplateResource::collection($application->form_templates));
		*/
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @param  string $application_slug
	 * @param  \Illuminate\Http\Request $request
	 *
	 * @return \Illuminate\Http\JsonResponse
	 * @throws \Illuminate\Validation\ValidationException
	 */
	public function store($application_slug, Request $request)
	{
		$this->validate($request, [
            'type_id' => 'nullable|integer|min:1',
			'name' => 'required|max:191'
		]);

		try {
            $user = Auth::user();
            if ($user->role->name == 'Super Admin') {
                $application = Application::where('slug', $application_slug)->first();
            } else {
                $application = $user->applications()->where('slug', $application_slug)->first();
            }

			// Check whether user has permission
			if (!$this->hasPermission($user, $application)) {
				return $this->returnError('application', 403, 'create form_templates');
			}

			// Send error if application does not exist
			if (!$application) {
				return $this->returnApplicationNameError();
			}

            $type_id = $request->input('type_id', null);
            if ($type_id) {
                // Send error if organisation does not exist
                if (!Type::find($type_id)) {
                    return $this->returnError('type', 404, 'create form template');
                }
            } else {
                $type_id = Type::where('name', 'organisation')->first()->id;
            }

			// Create form_template
            $form_template = $application->form_templates()->create([
                'type_id' => $type_id,
                'name' => $request->input('name'),
                'status_id' => Status::where('status', 'Open')->first()->id
            ]);

            $accessLevel = AccessLevel::whereValue('internal')->first();
            $form_template->accessSettings()->create([
                'access_level_id' => $accessLevel->id,
            ]);

			if ($form_template) {
				return $this->returnSuccessMessage('form_template', new FormTemplateResource(FormTemplate::find($form_template->id)));
			}

			// Send error if form_template is not created
			return $this->returnError('form_template', 503, 'create');
		} catch (Exception $e) {
			// Send error
			return $this->returnErrorMessage(503, $e->getMessage());
		}
	}
    /**
     * Duplicate a resource in storage.
     *
     * @param  string $application_slug
     * @param  int $id
     * @param  \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function duplicate($application_slug, $id, Request $request)
    {
        $this->validate($request, [
            'name' => 'required|max:191'
        ]);

        try {
            $user = Auth::user();
            if ($user->role->name == 'Super Admin') {
                $application = Application::where('slug', $application_slug)->first();
            } else {
                $application = $user->applications()->where('slug', $application_slug)->first();
            }

            // Check whether user has permission
            if (!$this->hasPermission($user, $application)) {
                return $this->returnError('application', 403, 'duplicate form_templates');
            }

            // Send error if application does not exist
            if (!$application) {
                return $this->returnApplicationNameError();
            }

            $form_template = $application->form_templates()->find($id);

            // Send error if form_template does not exist
            if (!$form_template) {
                return $this->returnError('form_template', 404, 'duplicate');
            }

            // Duplicate form template
            $new_form_template = $application->form_templates()->create([
                'name' => $request->input('name'),
                'type_id' => $form_template->type_id,
                'show_progress' => $form_template->show_progress,
                'allow_submit' => $form_template->allow_submit,
                'auto' => $form_template->auto,
                'status_id' => $form_template->status_id
            ]);

            if ($new_form_template) {
                $section_map = [];
                $question_map = [];
                $answer_map = [];

                $sections = $form_template->sections()->get();
                foreach ($sections as $section) {
                    $new_section = $new_form_template->sections()->create([
                        'name' => $section->name,
                        'parent_section_id' => $section->parent_section_id,
                        'order' => $section->order,
                        'repeatable' => $section->repeatable,
                        'max_rows' => $section->max_rows,
                        'min_rows' => $section->min_rows
                    ]);
                    $section_map[$section->id] = $new_section->id;

                    $questions = $section->questions()->get();
                    foreach ($questions as $question) {
                        $new_question = $new_section->questions()->create([
                            'question' => $question->question,
                            'description' => $question->description,
                            'mandatory' => $question->mandatory,
                            'question_type_id' => $question->question_type_id,
                            'order' => $question->order,
                            'width' => $question->width,
                            'sort_id' => $question->sort_id,
                            'key' => $question->key
                        ]);
                        $question_map[$question->id] = $new_question->id;

                        $validations = $question->validations()->get();
                        foreach ($validations as $validation) {
                            $new_question->validations()->create([
                                'form_template_id' => $new_form_template->id,
                                'validation_type_id' => $validation->validation_type_id,
                                'validation_data' => $validation->validation_data
                            ]);
                        }

                        $answers = $question->answers()->get();
                        foreach ($answers as $answer) {
                            $new_answer = $new_question->answers()->create([
                                'answer' => $answer->answer,
                                'parameter' => $answer->parameter,
                                'order' => $answer->order
                            ]);
                            $answer_map[$answer->id] = $new_answer->id;
                        }
                    }
                }

                $sections = $new_form_template->sections()->get();
                foreach ($sections as $new_section) {
                    $new_section->update([
                        'parent_section_id' => isset($new_section->parent_section_id) ? $section_map[$new_section->parent_section_id] : null
                    ]);
                }

                $triggers = $form_template->triggers()->get();
                foreach ($triggers as $trigger) {
                    $new_form_template->triggers()->create([
                        'type' => $trigger->type,
                        'question_id' => isset($trigger->question_id) ? ($trigger->type === 'Question' ? $question_map[$trigger->question_id] : $section_map[$trigger->question_id]) : null,
                        'parent_question_id' => isset($trigger->parent_question_id) ? $question_map[$trigger->parent_question_id] : null,
                        'parent_answer_id' => isset($trigger->parent_answer_id) ? $answer_map[$trigger->parent_answer_id] : null,
                        'value' => $trigger->value,
                        'comparator_id' => $trigger->comparator_id,
                        'order' => $trigger->order,
                        'operator' => $trigger->operator
                    ]);
                }

                return $this->returnSuccessMessage('form_template', new FormTemplateResource(FormTemplate::find($new_form_template->id)));
            }

            // Send error if form_template is not created
            return $this->returnError('form_template', 503, 'create');
        } catch (Exception $e) {
            // Send error
            return $this->returnErrorMessage(503, $e->getMessage());
        }
    }

	/**
	 * Display the specified resource.
	 *
	 * @param  string $application_slug
	 * @param  int $id
	 *
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function show($application_slug, $id)
	{
        $user = Auth::user();
        if ($user->role->name == 'Super Admin') {
            $application = Application::where('slug', $application_slug)->first();
        } else {
            $application = $user->applications()->where('slug', $application_slug)->first();
        }

		// Check whether user has permission
		if (!$this->hasPermission($user, $application)) {
			return $this->returnError('application', 403, 'view form_templates');
		}

		// Send error if application does not exist
		if (!$application) {
			return $this->returnApplicationNameError();
		}

		$form_template = $application->form_templates()->find($id);
		if ($form_template) {
			return $this->returnSuccessMessage('form_template', new FormTemplateResource($form_template));
		}

		// Send error if form_template does not exist
		return $this->returnError('form_template', 404, 'show');
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  string $application_slug
	 * @param  int $id
	 * @param  \Illuminate\Http\Request $request
	 *
	 * @return \Illuminate\Http\JsonResponse
	 * @throws \Illuminate\Validation\ValidationException
	 */
    public function update($application_slug, $id, Request $request)
    {
        $this->validate($request, [
            'type_id' => 'nullable|integer|min:1',
            'name' => 'filled|max:191',
            'show_progress' => 'filled|boolean',
            'csv' => 'file',
            'status_id' => 'nullable|integer|min:1',
            'type' => 'filled'
        ]);

        $user = Auth::user();
        $application = ApplicationRepository::bySlug($user, $application_slug);

        // Check whether user has permission
        if (!$this->hasPermission($user, $application)) {
            return $this->returnError('application', 403, 'update form_templates');
        }

        $form_template = $application->form_templates()->findOrFail($id);

        $data = $request->only('type_id', 'name', 'show_progress', 'status_id');
        $form_template->fill($data)->save();

        return $this->returnSuccessMessage('form_template', new FormTemplateResource(FormTemplate::find($form_template->id)));
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  string $application_slug
	 * @param  int $id
	 *
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function destroy($application_slug, $id)
	{
		try {
            $user = Auth::user();
            if ($user->role->name == 'Super Admin') {
                $application = Application::where('slug', $application_slug)->first();
            } else {
                $application = $user->applications()->where('slug', $application_slug)->first();
            }

			// Check whether user has permission
			if (!$this->hasPermission($user, $application)) {
				return $this->returnError('application', 403, 'delete form_templates');
			}

			// Send error if application does not exist
			if (!$application) {
				return $this->returnApplicationNameError();
			}

			$form_template = $application->form_templates()->find($id);

			// Send error if form_template does not exist
			if (!$form_template) {
				return $this->returnError('form_template', 404, 'delete');
			}

			if ($form_template->delete()) {
				return $this->returnSuccessMessage('message', 'Form Template has been deleted successfully.');
			}

			// Send error if there is an error on delete
			return $this->returnError('form_template', 503, 'delete');
		} catch (Exception $e) {
			// Send error
			return $this->returnErrorMessage(503, $e->getMessage());
		}
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @param  string $application_slug
	 * @param  \Illuminate\Http\Request $request
	 *
	 * @return \Illuminate\Http\JsonResponse
	 * @throws \Illuminate\Validation\ValidationException
	 */
	public function setAuto($application_slug, Request $request)
	{
		$this->validate($request, [
			'form_template_ids' => 'required|array',
			'form_template_ids.*' => 'integer'
		]);

		try {
            $user = Auth::user();
            if ($user->role->name == 'Super Admin') {
                $application = Application::where('slug', $application_slug)->first();
            } else {
                $application = $user->applications()->where('slug', $application_slug)->first();
            }

			// Send error if application does not exist
			if (!$application) {
				return $this->returnApplicationNameError();
			}

			$form_template_ids = $request->input('form_template_ids', []);
			$form_templates = $application->form_templates()->get();
			foreach ($form_templates as $form_template) {
				$form_template->auto = in_array($form_template->id, $form_template_ids);
				$form_template->save();
			}

			return $this->returnSuccessMessage('message', 'Auto fields are updated successfully.');
		} catch (Exception $e) {
			// Send error
			return $this->returnErrorMessage(503, $e->getMessage());
		}
	}

	/**
	 * Check whether user has permission or not
	 *
	 * @param  $user
	 * @param  $application
	 *
	 * @return bool
	 */
	protected function hasPermission($user, $application)
	{
		if ($user->role->name == 'Super Admin') {
			return true;
		}

		$role = ApplicationUser::where([
			'user_id' => $user->id,
			'application_id' => $application->id
		])->first()->role;

		if ($role->name != 'Admin') {
			return false;
		}

		return true;
	}
}
