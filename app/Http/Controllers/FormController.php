<?php

namespace App\Http\Controllers;

use Auth;
use Exception;
use App\User;
use App\Models\Application;
use App\Models\ApplicationUser;
use App\Models\Organisation;
use App\Models\FormTemplate;
use App\Models\Form;
use App\Models\Status;
use App\Http\Resources\SectionResource;
use App\Http\Resources\QuestionResource;
use App\Http\Resources\AnswerResource;
use App\Http\Resources\FormResource;
use App\Http\Resources\FormAllResource;
use App\Http\Resources\ResponseResource;
use App\Http\Resources\ApplicationUserResource;
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

		// Check Application
		if($user->role->name === 'Super Admin') {
			$application = Application::where('slug', $application_slug)->first();
		} else {
			$application = $user->applications()->where('slug', $application_slug)->first();
		}

		// No Application
		if(!$application) {
			return $this->returnApplicationNameError();
		}

		// Get forms
		$form_templates = $application->form_templates->pluck('id');
		if($this->hasPermission($user, $application->id)) {
            $forms = Form::with(['form_template', 'user', 'organisation', 'responses'])->get()->whereIn('form_template_id', $form_templates);
		} else {
			$user->load(['forms.form_template', 'forms.user', 'forms.organisation', 'forms.responses']);
			$forms = $user->forms()->whereIn('form_template_id', $form_templates)->get();
		}

		return $this->returnSuccessMessage('forms', FormResource::collection($forms));
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
        if ($user->role->name == 'Super Admin') {
            $application = Application::where('slug', $application_slug)->first();
        } else {
            $application = $user->applications()->where('slug', $application_slug)->first();
        }

        // Send error if application does not exist
        if (!$application) {
            return $this->returnApplicationNameError();
        }

        $form = Form::find($id);

        // Send error if form does not exist
        if (!$form) {
            return $this->returnError('form', 404, 'get form');
        }

        $form_template = $form->form_template;

        // Send error if form template does not exist
        if (!$form_template) {
            return $this->returnError('form_template', 404, 'get form');
        }

        if ($form_template->application->slug !== $application_slug) {
            return $this->returnError('application', 404, 'get form');
        }

        return $this->returnSuccessMessage('form', new FormResource(Form::with(['form_template', 'responses'])->find($form->id)));
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
		$this->validate($request, [
			'user_id' => 'nullable|integer|min:1',
			'organisation_id' => 'nullable|integer|min:1',
			'progress' => 'filled|integer|min:0'
		]);

		try {
			$form_template = FormTemplate::find($form_template_id);

			// Send error if form template does not exist
			if (!$form_template) {
				return $this->returnError('form template', 404, 'create form');
			}

            // Check whether user has permission
            $user = Auth::user();
            if ($form_template->status->status == 'Open' && !$this->hasPermission($user, $form_template->application_id)) {
                return $this->returnError('application', 403, 'create form');
            }

			$user_id = $request->input('user_id', null);
			if ($user_id) {
				// Send error if organisation does not exist
				if (!User::find($user_id)) {
					return $this->returnError('user', 404, 'create form');
				}
			} else {
				$user_id = Auth::user()->id;
			}

			$organisation_id = $request->input('organisation_id', null);
			if ($organisation_id) {
				// Send error if organisation does not exist
				if (!Organisation::find($organisation_id)) {
					return $this->returnError('organisation', 404, 'create form');
				}
			}

			// Create form
			$form = $form_template->forms()->create([
				'user_id' => $user_id,
				'organisation_id' => $organisation_id,
				'progress' => $request->input('progress', 0),
				'period_start' => $request->input('period_start', null),
				'period_end' => $request->input('period_end', null),
				'status_id' => Status::where('status', 'Open')->first()->id
			]);

			if ($form) {
				return $this->returnSuccessMessage('form', new FormResource(Form::find($form->id)));
			}

			// Send error if form is not created
			return $this->returnError('form', 503, 'create');
		} catch (Exception $e) {
			// Send error
			return $this->returnErrorMessage(503, $e->getMessage());
		}
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
