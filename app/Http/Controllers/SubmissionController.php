<?php

namespace App\Http\Controllers;

use Auth;
use Exception;
use App\User;
use App\Models\Application;
use App\Models\ApplicationUser;
use App\Models\Team;
use App\Models\Form;
use App\Models\Submission;
use App\Models\Status;
use App\Http\Resources\SectionResource;
use App\Http\Resources\QuestionResource;
use App\Http\Resources\AnswerResource;
use App\Http\Resources\SubmissionResource;
use App\Http\Resources\SubmissionAllResource;
use App\Http\Resources\ResponseResource;
use App\Http\Resources\ApplicationUserResource;
use Illuminate\Http\Request;

class SubmissionController extends Controller
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
	 * @param  int $form_id
	 *
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function index($form_id)
	{
		$user = Auth::user();

		$submissions = $user->submissions()->where('form_id', $form_id)->get();

		if ($user->role->name == 'Super Admin') {
			$submissions = Submission::where('form_id', $form_id)->get();
		}

		return $this->returnSuccessMessage('submissions', SubmissionResource::collection($submissions));
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
        if ($user->role->name == 'Super Admin') {
            $application = Application::where('slug', $application_slug)->first();
        } else {
            $application = $user->applications()->where('slug', $application_slug)->first();
        }

		// Send error if application does not exist
		if (!$application) {
			return $this->returnApplicationNameError();
		}

		$forms = $application->forms()->get();
		$submissions = null;

		foreach ($forms as $form) {
			$form_submissions = $user->submissions()->where('form_id', $form->id)->get();

			if ($this->hasPermission($user, $application->id)) {
				$form_submissions = Submission::where('form_id', $form->id)->get();
			}

			if ($submissions) {
				$submissions = $submissions->merge($form_submissions);
			} else {
				$submissions = $form_submissions;
			}
		}

		return $this->returnSuccessMessage('submissions', $submissions ? SubmissionAllResource::collection($submissions) : []);
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

        $submission = Submission::find($id);

        // Send error if submission does not exist
        if (!$submission) {
            return $this->returnError('submission', 404, 'get submission');
        }

        $form = $submission->form;

        // Send error if form does not exist
        if (!$form) {
            return $this->returnError('form', 404, 'get submission');
        }

        if ($form->application->slug !== $application_slug) {
            return $this->returnError('application', 404, 'get submission');
        }

        return $this->returnSuccessMessage('submission', new SubmissionResource(Submission::find($submission->id)));
    }

	/**
	 * Store a newly created resource in storage.
	 *
	 * @param  int $form_id
	 * @param  \Illuminate\Http\Request $request
	 *
	 * @return \Illuminate\Http\JsonResponse
	 * @throws \Illuminate\Validation\ValidationException
	 */
	public function store($form_id, Request $request)
	{
		$this->validate($request, [
			'user_id' => 'nullable|integer|min:1',
			'team_id' => 'nullable|integer|min:1',
			'progress' => 'filled|integer|min:0',
			'period_start' => 'nullable|date',
			'period_end' => 'nullable|date'
		]);

		try {
			$form = Form::find($form_id);

			// Send error if form does not exist
			if (!$form) {
				return $this->returnError('form', 404, 'create submission');
			}

			$user_id = $request->input('user_id', null);
			if ($user_id) {
				// Send error if team does not exist
				if (!User::find($user_id)) {
					return $this->returnError('user', 404, 'create submission');
				}
			} else {
				$user_id = Auth::user()->id;
			}

			$team_id = $request->input('team_id', null);
			if ($team_id) {
				// Send error if team does not exist
				if (!Team::find($team_id)) {
					return $this->returnError('team', 404, 'create submission');
				}
			}

			// Create submission
			$submission = $form->submissions()->create([
				'user_id' => $user_id,
				'team_id' => $team_id,
				'progress' => $request->input('progress', 0),
				'period_start' => $request->input('period_start', $form->period_start),
				'period_end' => $request->input('period_end', $form->period_end),
				'status_id' => Status::where('status', 'Open')->first()->id
			]);

			if ($submission) {
				return $this->returnSuccessMessage('submission', new SubmissionResource(Submission::find($submission->id)));
			}

			// Send error if submission is not created
			return $this->returnError('submission', 503, 'create');
		} catch (Exception $e) {
			// Send error
			return $this->returnErrorMessage(503, $e->getMessage());
		}
	}

    /**
     * Duplicate a resource in storage.
     *
     * @param  int $form_id
     * @param  int $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function duplicate($form_id, $id)
    {
        try {
            $form = Form::find($form_id);

            // Send error if section does not exist
            if (!$form) {
                return $this->returnError('form', 404, 'create submission');
            }

            $submission = $form->submissions()->find($id);

            // Send error if question does not exist
            if (!$submission) {
                return $this->returnError('submission', 404, 'duplicate');
            }

            // Duplicate submission
            $newSubmission = $form->submissions()->create([
                'user_id' => $submission->user_id,
                'team_id' => $submission->team_id,
                'progress' => $submission->progress,
                'period_start' => $submission->period_start,
                'period_end' => $submission->period_end,
                'status_id' => $submission->status_id
            ]);

            if ($newSubmission) {
                // Duplicate children responses
                $submission->responses()->get()->each(function ($response) use ($newSubmission) {
                    $newSubmission->responses()->create([
                        'question_id' => $response->question_id,
                        'response' => $response->response,
                        'answer_id' => $response->answer_id,
                        'order' => $response->order
                    ]);
                });

                return $this->returnSuccessMessage('submission', new SubmissionResource(Submission::find($newSubmission->id)));
            }

            // Send error if question is not created
            return $this->returnError('submission', 503, 'duplicate');
        } catch (Exception $e) {
            // Send error
            return $this->returnErrorMessage(503, $e->getMessage());
        }
    }

	/**
	 * Display the specified resource.
	 *
	 * @param  int $form_id
	 * @param  int $id
	 *
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function show($form_id, $id)
	{
		$form = Form::find($form_id);

		// Send error if form does not exist
		if (!$form) {
			return $this->returnError('form', 404, 'show submission');
		}

		if ($submission) {
			$user = Auth::user();
			if ($this->hasPermission($user, $form->application_id) || $submission->user_id != $user->id) {
				return $this->returnError('submission', 403, 'see');
			}

			return $this->returnSuccessMessage('submission', new SubmissionResource($submission));
		}

		// Send error if submission does not exist
		return $this->returnError('submission', 404, 'show');
	}

	/**
	 * Display the specified resource.
	 *
	 * @param  int $form_id
	 * @param  int $id
	 *
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function getData($form_id, $id)
	{
		$form = Form::find($form_id);

		// Send error if form does not exist
		if (!$form) {
			return $this->returnError('form', 404, 'show submission');
		}

		$submission = $form->submissions()->find($id);
		if ($submission) {
			$user = Auth::user();
			if ($this->hasPermission($user, $form->application_id) || $submission->user_id != $user->id) {
				return $this->returnError('submission', 403, 'see');
			}

			$data = SectionResource::collection($form->sections()->get());
			foreach ($data as $section) {
				$questions = QuestionResource::collection($section->questions()->get());
				foreach ($questions as $question) {
					$answers = AnswerResource::collection($question->answers()->get());
					foreach ($answers as $answer) {
						$answer['responses'] = ResponseResource::collection($answer->responses()->where([
							['submission_id', '=', $id],
							['question_id', '=', $question->id]
						]));
					}
				}
			}

			return $this->returnSuccessMessage('data', $data);
		}

		// Send error if submission does not exist
		return $this->returnError('submission', 404, 'show');
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  int $form_id
	 * @param  int $id
	 * @param  \Illuminate\Http\Request $request
	 *
	 * @return \Illuminate\Http\JsonResponse
	 * @throws \Illuminate\Validation\ValidationException
	 */
	public function update($form_id, $id, Request $request)
	{
		$this->validate($request, [
			'user_id' => 'nullable|integer|min:1',
			'team_id' => 'nullable|integer|min:1',
			'progress' => 'filled|integer|min:0',
			'period_start' => 'nullable|date',
			'period_end' => 'nullable|date',
			'status_id' => 'filled|integer|min:1'
		]);

		try {
			$form = Form::find($form_id);

			// Send error if form does not exist
			if (!$form) {
				return $this->returnError('form', 404, 'update submission');
			}

			$user = Auth::user();
			
			$submission = $form->submissions()->where([
				'id' => $id,
				'user_id' => $user->id
			])->first();

			if (!$submission && $this->hasPermission($user, $form->application_id)) {
				$submission = $form->submissions()->where([
					'id' => $id
				])->first();
			}

			// Send error if submission does not exist
			if (!$submission) {
				return $this->returnError('submission', 404, 'update');
			}

			// Check whether the question type exists or not
			$status_id = $request->input('status_id', null);
			if ($status_id && !Status::find($status_id)) {
				return $this->returnError('status', 404, 'update submission');
			}

			// Update submission
			if ($submission->fill($request->only('user_id', 'team_id', 'progress', 'period_start', 'period_end', 'status_id'))->save()) {
				$submission->touch();
				return $this->returnSuccessMessage('submission', new SubmissionResource(Submission::find($submission->id)));
			}

			// Send error if there is an error on update
			return $this->returnError('submission', 503, 'update');
		} catch (Exception $e) {
			// Send error
			return $this->returnErrorMessage(503, $e->getMessage());
		}
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int $form_id
	 * @param  int $id
	 *
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function destroy($form_id, $id)
	{
		try {
			$form = Form::find($form_id);
			$user = Auth::user();

			// Send error if form does not exist
			if (!$form) {
				return $this->returnError('form', 404, 'delete submission');
			}

			$submission = $form->submissions()->where([
				'id' => $id,
				'user_id' => $user->id
			])->first();

			if ($this->hasPermission($user, $form->application_id)) {
				$submission = $form->submissions()->where([
					'id' => $id
				])->first();
			}

			// Send error if submission does not exist
			if (!$submission) {
				return $this->returnError('submission', 404, 'delete');
			}

			if ($submission->delete()) {
				return $this->returnSuccessMessage('message', 'Submission has been deleted successfully.');
			}

			// Send error if there is an error on delete
			return $this->returnError('submission', 503, 'delete');
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
