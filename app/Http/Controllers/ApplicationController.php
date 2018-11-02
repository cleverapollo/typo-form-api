<?php

namespace App\Http\Controllers;

use Auth;
use Exception;
use Storage;
use App\Models\Role;
use App\Models\Type;
use App\Models\Status;
use App\Models\Application;
use App\Models\ApplicationUser;
use App\Models\Invitation;
use App\Models\Comparator;
use App\Models\QuestionType;
use App\Http\Resources\UserResource;
use App\Http\Resources\ApplicationResource;
use App\Http\Resources\ApplicationUserResource;
use App\Http\Resources\InvitationResource;
use App\Http\Resources\SubmissionResource;
use App\Jobs\UsersNotification;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ApplicationController extends Controller
{
	/**
	 * Create a new controller instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		$this->middleware('auth:api', ['except' => ['show']]);
	}

	/**
	 * Display a listing of the resource.
	 *
     * @param  \Illuminate\Http\Request $request
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function index(Request $request)
	{
		$user = Auth::user();
		if ($user->role->name == 'Super Admin') {
			$applications = Application::get();
		} else {
            $this->acceptInvitation('application');
            $this->acceptInvitation('team');

            $origin = $request->header('Origin');
            if (strlen($origin)) {
                $request_slug = explode('.', explode('://', $origin)[1])[0];
                $request_application = Application::where('slug', $request_slug)->first();
                if ($request_application && $request_application->join_flag) {
                    ApplicationUser::firstOrCreate([
                        'user_id' => $user->id,
                        'application_id' => $request_application->id,
                        'role_id' => Role::where('name', 'User')->first()->id
                    ]);
                }
            }

            $applications = $user->applications()->get();
        }

		return $this->returnSuccessMessage('applications', ApplicationResource::collection($applications));
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @param  \Illuminate\Http\Request $request
	 *
	 * @return \Illuminate\Http\JsonResponse
	 * @throws \Illuminate\Validation\ValidationException
	 */
	public function store(Request $request)
	{
		$this->validate($request, [
			'name' => 'required|unique:applications|max:191'
		]);

		try {
			// Check whether user is SuperAdmin or not
			$user = Auth::user();
			if ($user->role->name != 'Super Admin') {
				return $this->returnError('application', 403, 'create applications');
			}

			$share_token = base64_encode(str_random(40));
			while (!is_null(Application::where('share_token', $share_token)->first())) {
				$share_token = base64_encode(str_random(40));
			}

			$name = $request->input('name');
            // $patterns = ['/ /', '/\$/', '/&/', '/\+/', '/,/', '/\//', '/:/', '/;/', '/\?/', '/=/', '/@/', '/>/', '/</', '/#/', '/%/', '/{/', '/}/', '/\|/', '/\^/', '/~/', '/\[/', '/\]/', '/\`/'];
            // $replacements = [];
            // $slug = strtolower(preg_replace($patterns, $replacements, stripslashes($name)));
            $slug = strtolower(preg_replace('/\s|\$|&|\+|,|\/|:|;|\?|=|@|>|<|#|%|{|}|\||\^|~|\[|\]|\`/', '', stripslashes($name)));
			if (Application::where('slug', $slug)->count() > 0) {
				return response()->json([
					'slug' => ['The slug has already been taken.']
				], 422);
			}

			// Create application
			$application = Application::create([
				'name' => $name,
				'slug' => $slug,
				'share_token' => $share_token
			]);

			if ($application) {
				return $this->returnSuccessMessage('application', new ApplicationResource(Application::find($application->id)));
			}

			// Send error if application is not created
			return $this->returnError('application', 503, 'create');
		} catch (Exception $e) {
			// Send error
			return $this->returnErrorMessage(503, $e->getMessage());
		}
	}

	/**
	 * Store a newly created email resource in storage.
	 *
	 * @param  $application
	 * @param  $email
	 *
	 * @return \Exception
	 */
	public function createApplicationEmail($application, $email)
	{
		try {
			// Create application email
			$application_email = $application->emails()->create([
				'recipients' => $email,
				'subject' => 'Create submission',
				'body' => 'Submission is created successfully. Please fill out the form and send submission.',
			]);

			if (!$application_email) {
				throw new Exception('Cannot create application email.');
			}
		} catch (Exception $e) {
			// Send error
			return $e;
		}
	}

	/**
	 * Display the specified resource.
	 *
	 * @param  string $application_slug
	 *
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function show($application_slug)
	{
		$user = Auth::user();
		if (!$user || $user->role->name == 'Super Admin') {
			$application = Application::where('slug', $application_slug)->first();
		} else {
			$application = $user->applications()->where('slug', $application_slug)->first();
		}

		if ($application) {
			return $this->returnSuccessMessage('application', new ApplicationResource($application));
		}

		// Send error if application does not exist
		return $this->returnError('application', 404, 'show');
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  string $application_slug
	 * @param  \Illuminate\Http\Request $request
	 *
	 * @return \Illuminate\Http\JsonResponse
	 * @throws \Illuminate\Validation\ValidationException
	 */
	public function update($application_slug, Request $request)
	{
		$this->validate($request, [
			'name' => 'filled|unique:applications|max:191'
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
				return $this->returnError('application', 404, 'update');
			}

			// Check whether user has permission to update
			if (!$this->hasPermission($user, $application)) {
				return $this->returnError('application', 403, 'update');
			}

			// Update application
			$name = $request->input('name');
			if ($name) {
                // $patterns = ['/ /', '/\$/', '/&/', '/\+/', '/,/', '/\//', '/:/', '/;/', '/\?/', '/=/', '/@/', '/>/', '/</', '/#/', '/%/', '/{/', '/}/', '/\|/', '/\^/', '/~/', '/\[/', '/\]/', '/\`/'];
                // $replacements = [];
                // $slug = strtolower(preg_replace($patterns, $replacements, stripslashes($name)));
                $slug = strtolower(preg_replace('/\s|\$|&|\+|,|\/|:|;|\?|=|@|>|<|#|%|{|}|\||\^|~|\[|\]|\`/', '', stripslashes($name)));
				if (Application::where('slug', $slug)->count() > 0) {
					return response()->json([
						'slug' => ['The slug has already been taken.']
					], 422);
				}
				$application->slug = $slug;
			}

			if ($application->fill($request->only('name', 'css', 'icon', 'logo', 'primary_color', 'secondary_color', 'background_image', 'support_text', 'join_flag'))->save()) {
				return $this->returnSuccessMessage('application', new ApplicationResource(Application::find($application->id)));
			}

			// Send error if there is an error on update
			return $this->returnError('application', 503, 'update');
		} catch (Exception $e) {
			// Send error
			return $this->returnErrorMessage(503, $e->getMessage());
		}
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  string $application_slug
	 *
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function destroy($application_slug)
	{
		try {
			$user = Auth::user();
			if ($user->role->name == 'Super Admin') {
				$application = Application::where('slug', $application_slug)->first();
			} else {
                $application = $user->applications()->where('slug', $application_slug)->first();
            }

			// Check whether user has permission to delete
			if (!$this->hasPermission($user, $application)) {
				return $this->returnError('application', 403, 'delete');
			}

			// Delete Application
			if ($application->delete()) {
				return $this->returnSuccessMessage('message', 'Application has been deleted successfully.');
			}

			// Send error if there is an error on delete
			return $this->returnError('application', 503, 'delete');
		} catch (Exception $e) {
			// Send error
			return $this->returnErrorMessage(503, $e->getMessage());
		}
	}

	/**
	 * Join to the Application.
	 *
	 * @param  string $token
	 *
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function join($token)
	{
		return $this->acceptJoin('application', $token);
	}

	/**
	 * Get users for the Application.
	 *
	 * @param  string $application_slug
	 *
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function getUsers($application_slug)
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

		// Check whether user has permission to get
		if (!$this->hasPermission($user, $application)) {
			return $this->returnError('application', 403, 'see the users of');
		}

		$currentUsers = $application->users()->get();

        $type = Type::where('name', 'application')->first();
		$invitedUsers = Invitation::where([
			'reference_id' => $application->id,
			'status' => 0,
            'type_id' => $type->id
		])->get();

		return $this->returnSuccessMessage('users', [
			'current' => UserResource::collection($currentUsers),
			'unaccepted' => InvitationResource::collection($invitedUsers)
		]);
	}

	/**
	 * Invite users to the Application.
	 *
	 * @param  string $application_slug
	 * @param  \Illuminate\Http\Request $request
	 *
	 * @return \Illuminate\Http\JsonResponse
	 * @throws \Illuminate\Validation\ValidationException
	 */
	public function inviteUsers($application_slug, Request $request)
	{
		$this->validate($request, [
			'invitations' => 'array',
			'invitations.*.email' => 'required|email',
			'invitations.*.application_role_id' => 'required|integer|min:2'
		]);

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

		// Check whether user has permission to send invitation
		if (!$this->hasPermission($user, $application)) {
			return $this->returnError('application', 403, 'send invitation');
		}

		$invitations = $request->input('invitations', []);
		$host = $request->header('Origin');

		// Send invitation
		$this->sendInvitation('application', $application, $invitations, $host);

		return $this->returnSuccessMessage('message', 'Invitation has been sent successfully.');
	}

	/**
	 * Update user role in the Application.
	 *
	 * @param  string $application_slug
	 * @param  int $id
	 * @param  Request $request
	 *
	 * @return \Illuminate\Http\JsonResponse
	 * @throws \Illuminate\Validation\ValidationException
	 */
	public function updateUser($application_slug, $id, Request $request)
	{
		$this->validate($request, [
			'application_role_id' => 'required|integer|min:2'
		]);

		try {
			// Check whether the role exists or not
			$role = Role::find($request->input('application_role_id'));
			if (!$role) {
				return $this->returnError('role', 404, 'update user');
			}

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

			$application_user = ApplicationUser::where([
				'user_id' => $id,
				'application_id' => $application->id
			])->first();

			// Send error if user does not exist in the application
			if (!$application_user) {
				return $this->returnError('user', 404, 'update role');
			}

			// Check whether user has permission to update
			if (!$this->hasPermission($user, $application)) {
				return $this->returnError('application', 403, 'update user');
			}

			// Update user role
			if ($application_user->fill(['role_id' => $role->id])->save()) {
				dispatch(new UsersNotification([
					'users' => [$application_user],
					'message' => 'Application user role has been updated successfully.'
				]));

				return $this->returnSuccessMessage('user', new ApplicationUserResource(ApplicationUser::find($application_user->id)));
			}

			// Send error if there is an error on update
			return $this->returnError('user role', 503, 'update');
		} catch (Exception $e) {
			// Send error
			return $this->returnErrorMessage(503, $e->getMessage());
		}
	}

	/**
	 * Delete user from the Application.
	 *
	 * @param  string $application_slug
	 * @param  int $id
	 *
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function deleteUser($application_slug, $id)
	{
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

			$application_user = ApplicationUser::where([
				'user_id' => $id,
				'application_id' => $application->id
			])->first();

			// Send error if user does not exist in the application
			if (!$application_user) {
				return $this->returnError('user', 404, 'delete');
			}

			// Check whether user has permission to delete
			if (!$this->hasPermission($user, $application)) {
				return $this->returnError('application', 403, 'delete user');
			}

			if ($application_user->delete()) {
				return $this->returnSuccessMessage('message', 'User has been removed from application successfully.');
			}

			// Send error if there is an error on delete
			return $this->returnError('user', 503, 'delete');
		} catch (Exception $e) {
			// Send error
			return $this->returnErrorMessage(503, $e->getMessage());
		}
	}

	/**
	 * Update invited user role in the Application.
	 *
	 * @param  string $application_slug
	 * @param  int $id
	 * @param  Request $request
	 *
	 * @return \Illuminate\Http\JsonResponse
	 * @throws \Illuminate\Validation\ValidationException
	 */
	public function updateInvitedUser($application_slug, $id, Request $request)
	{
		$this->validate($request, [
			'application_role_id' => 'required|integer|min:2'
		]);

		try {
			// Check whether the role exists or not
			$role = Role::find($request->input('application_role_id'));
			if (!$role) {
				return $this->returnError('role', 404, 'update invited user');
			}

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

            $type = Type::where('name', 'application')->first();
			$application_invitation = Invitation::where([
				'id' => $id,
				'reference_id' => $application->id,
                'type_id' => $type->id
			])->first();

			// Send error if invited user does not exist in the application
			if (!$application_invitation) {
				return $this->returnError('user', 404, 'update role');
			}

			// Check whether user has permission to update
			if (!$this->hasPermission($user, $application)) {
				return $this->returnError('application', 403, 'update user');
			}

			// Update user role
			if ($application_invitation->fill(['role_id' => $role->id])->save()) {
				return $this->returnSuccessMessage('user', new InvitationResource(Invitation::find($application_invitation->id)));
			}

			// Send error if there is an error on update
			return $this->returnError('user role', 503, 'update');
		} catch (Exception $e) {
			// Send error
			return $this->returnErrorMessage(503, $e->getMessage());
		}
	}

	/**
	 * Delete invited user from the Application.
	 *
	 * @param  string $application_slug
	 * @param  int $id
	 *
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function deleteInvitedUser($application_slug, $id)
	{
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

            $type = Type::where('name', 'application')->first();
			$application_invitation = Invitation::where([
				'id' => $id,
				'reference_id' => $application->id,
                'type_id' => $type->id
			])->first();

			// Send error if invited user does not exist in the application
			if (!$application_invitation) {
				return $this->returnError('user', 404, 'delete');
			}

			// Check whether user has permission to delete
			if (!$this->hasPermission($user, $application)) {
				return $this->returnError('application', 403, 'delete invited user');
			}

			if ($application_invitation->delete()) {
				return $this->returnSuccessMessage('message', 'Invited User has been removed from application successfully.');
			}

			// Send error if there is an error on delete
			return $this->returnError('user', 503, 'delete');
		} catch (Exception $e) {
			// Send error
			return $this->returnErrorMessage(503, $e->getMessage());
		}
	}

	public function exportCSV($application_slug)
    {
        try {
            $user = Auth::user();
            if ($user->role->name == 'Super Admin') {
                $application = Application::where('slug', $application_slug)->first();
            } else {
                $application = $user->applications()->where('slug', $application_slug)->first();
            }

            // Check whether user has permission to delete
            if (!$this->hasPermission($user, $application)) {
                return $this->returnError('application', 403, 'export');
			}

			//Application
			ini_set('max_execution_time', 0);
			$data = [];
			$application->load(['users', 'teams', 'forms.submissions.status', 'forms.sections.questions.answers', 'forms.sections.questions.responses']);
			$data['Applications'][$application->id] = array_map(function($item) { return is_array($item) ? null : $item; }, $application->toArray());

			//Users
			foreach($application->users as $user) {
				$user_details = array_intersect_key($user->toArray(), array_flip(['id', 'first_name', 'last_name', 'email']));
				$application_user_details = array_intersect_key($user->pivot->toArray(), array_flip(['role_id', 'created_at', 'updated_at']));
				$data['Users'][$user->id] = array_merge($user_details, $application_user_details);
			}

			//Teams
			foreach($application->teams as $team) {
				$data['Teams'][$team->id] = array_map(function($item) { return is_array($item) ? null : $item; }, $team->toArray());
			}

			//Forms
			foreach($application->forms as $form) {
				$data['Forms'][$form->id] = array_map(function($item) { return is_array($item) ? null : $item; }, $form->toArray());

				//Submissions
				foreach($form->submissions as $submission) {
					$data['Submissions'][$submission->id] = array_map(function($item) { return is_array($item) ? null : $item; }, $submission->toArray());
				}			

				//Sections
				foreach($form->sections as $section) {
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

			foreach($data['Forms'] as $form) {
				foreach($data['Submissions'] as $submission) {
					if($submission['form_id'] === $form['id']) {
						foreach($data['Responses'] as $response) {
							if($response['submission_id'] === $submission['id']) {
								$row = [
									'form_id' => $form['id'],
									'form' => $form['name'],
									'submission_id' => $response['submission_id'],
									'submission_created' => $submission['created_at'],
									'submission_progress' => $submission['progress'],
									'submission_status' => Status::find($submission['status_id'])->status,
									'user_id' => $submission['user_id'],
									'first_name' => $data['Users'][$submission['user_id']]['first_name'] ?? '',
									'last_name' => $data['Users'][$submission['user_id']]['last_name'] ?? '',
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

								$data[$form['name']][] = $row;
							}
						}
					}
				}
			}

			//Create excel document
            $file = Excel::create($application->name, function ($excel) use ($data) {
				
				//Add each element from the data array
				foreach($data as $key=>$val) {
					$excel->sheet($key, function ($sheet) use ($data, $key) {
						$sheet->fromArray($data[$key]);
					});
				}
			})->string('xlsx');

			$filename = $application->name . '.xlsx';
			Storage::put('exports/' . $filename, $file);

			$file = [];
            $file['size'] = Storage::size('exports/' . $filename);
            $file['name'] = $filename;
            $file['url'] = Storage::url('exports/' . $filename);
            $file['stored_name'] = $filename;

			return $this->returnSuccessMessage('file', $file);
        } catch (Exception $e) {
            return $this->returnErrorMessage(503, $e->getMessage());
        }
    }

    /**
     * Filter submissions
     *
     * @param  $application_slug
     * @param  Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function filterSubmission($application_slug, Request $request)
    {
        $this->validate($request, [
            'filters' => 'array',
            'filters.*.source' => 'required',
            'filters.*.query' => 'filled|integer',
            'filters.*.question_id' => 'filled|integer'
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

            // ToDo: check admin

            $filters = $request->input('filters');
            $comparisons = [];
            $names = [];
            $questions = [];
            foreach ($filters as $key => $filter) {
                if ($filter['query']) {
                    $query = Comparator::find($filter['query']);
                    if ($query) {
                        $comparison = $this->getComparator($query->comparator, $filter['value']);
                        $comparison['source'] = $filter['source'];

                        if ($comparison['source'] == 'Question') {
                            if ($filter['question_id']) {
                                $comparison['question_id'] = $filter['question_id'];
                                $questions[] = $comparison;
                            }
                        } else if ($comparison['source'] == 'Form' || $comparison['source'] == 'User' || $comparison['source'] == 'Team' || $comparison['source'] == 'Status') {
                            $names[] = $comparison;
                        } else {
                        	if ($comparison['source'] == 'ID') {
                        		$comparison['source'] = 'id';
                        	} else if ($comparison['source'] == 'Form ID') {
                        		$comparison['source'] = 'form_id';
                        	} else if ($comparison['source'] == 'User ID') {
                        		$comparison['source'] = 'user_id';
                        	} else if ($comparison['source'] == 'Team ID') {
                        		$comparison['source'] = 'team_id';
                        	} else if ($comparison['source'] == 'Progress') {
                        		$comparison['source'] = 'progress';
                        	} else if ($comparison['source'] == 'Period Start') {
                        		$comparison['source'] = 'period_start';
                        	} else if ($comparison['source'] == 'Period End') {
                        		$comparison['source'] = 'period_end';
                        	} else if ($comparison['source'] == 'Created Date') {
                        		$comparison['source'] = 'created_at';
                        	} else if ($comparison['source'] == 'Updated Date') {
                        		$comparison['source'] = 'updated_at';
                        	}
                        	$comparisons[] = $comparison;
                        }
                    }
                }
            }

            $forms = $application->forms()->get();
            $submissionIds = [];
            $submissionCollection = new Collection();
            foreach ($forms as $form) {
                $submissions = $form->submissions;
                foreach ($comparisons as $comparison) {
                    switch ($comparison['query']) {
                        case 'is null':
                            $submissions = $submissions->filter(function ($item) use ($comparison) {
								return $item[$comparison['source']] === null;
							});
                            break;
                        case 'is not null':
                            $submissions = $submissions->filter(function ($item) use ($comparison) {
								return $item[$comparison['source']] !== null;
							});
                            break;
                        case 'in list':
                            $submissions = $submissions->filter(function ($item) use ($comparison) {
								return in_array($item[$comparison['source']], explode(',', $comparison['value']));
							});
                            break;
                        case 'not in list':
                            $submissions = $submissions->filter(function ($item) use ($comparison) {
								return !in_array($item[$comparison['source']], explode(',', $comparison['value']));
							});
                            break;
                        case '=':
                            $submissions = $submissions->filter(function ($item) use ($comparison) {
								return $item[$comparison['source']] == $comparison['value'];
							});
                            break;
                        case '!=':
                            $submissions = $submissions->filter(function ($item) use ($comparison) {
								return $item[$comparison['source']] != $comparison['value'];
							});
                            break;
                        case '<':
                            $submissions = $submissions->filter(function ($item) use ($comparison) {
								return $item[$comparison['source']] < $comparison['value'];
							});
                            break;
                        case '>':
                            $submissions = $submissions->filter(function ($item) use ($comparison) {
								return $item[$comparison['source']] > $comparison['value'];
							});
                            break;
                        case '<=':
                            $submissions = $submissions->filter(function ($item) use ($comparison) {
								return $item[$comparison['source']] <= $comparison['value'];
							});
                            break;
                        case '>=':
                            $submissions = $submissions->filter(function ($item) use ($comparison) {
								return $item[$comparison['source']] >= $comparison['value'];
							});
                            break;
                        default:
                            break;
                    }
                }

                $submissions = $submissions->all();

                foreach ($submissions as $submission) {
                    $invalid = false;
                    $responseCollection = new Collection();

                    foreach ($names as $name) {
                    	$name_str = '';

                    	if ($name['source'] == 'Form') {
                    		$name_str = $submission->form->name;
                    	} else if ($name['source'] == 'Team') {
                    		$name_str = $submission->team ? $submission->team->name : null;
                    	} else if ($name['source'] == 'User') {
                    		$name_str = $submission->user->first_name + ' ' + $submission->name->last_name;
                    	} else if ($name['source'] == 'Status') {
                    		$name_str = $usbmission->status->status;
                    	}

                    	$result = true;
                    	switch ($name['query']) {
                            case 'is null':
                                $result = ($name_str == null);
                                break;
                            case 'is not null':
                                $result = ($name_str != null);
                                break;
                            case 'in list':
                            	$result = in_array($name_str, explode(',', $name['value']));
                                break;
                            case 'not in list':
                                $result = !in_array($name_str, explode(',', $name['value']));
                                break;
                            case 'equals':
                            	$result = ($name_str == $name['value']);
                            	break;
                            case 'not equal to':
                            	$result = ($name_str != $name['value']);
                            	break;
                            case 'contains':
                            	$result = strpos($name_str, $name['value']);
                            	break;
                            case 'does not contain':
                            	$result = !strpos($name_str, $name['value']);
                            	break;
                            case 'starts with':
                            	$result = (substr($name_str, 0, strlen($name['value'])) == $name['value']);
                            	break;
                            case 'ends with':
                            	$result = (substr($name_str, -strlen($name['value'])) == $name['value']);
                            	break;
                            default:
                                $result = true;
                        }

                        if (!$result) {
                        	$invalid = true;
                        }

                    }

                    foreach ($questions as $question) {
                        $responses = $submission->responses->where('question_id', $question['question_id']);
                        switch ($question['query']) {
                            case 'is null':
                                $responses = $responses->whereNull('response');
                                break;
                            case 'is not null':
                                $responses = $responses->whereNotNull('response');
                                break;
                            case 'in list':
                                $responses = $responses->whereIn('response', explode(',', $question['value']));
                                break;
                            case 'not in list':
                                $responses = $responses->whereNotIn('response', explode(',', $question['value']));
                                break;
                            case '':
                                break;
                            default:
                                $responses = $responses->where('response', $question['query'], $question['value']);
                        }

                        if (count($responses->all()) == 0) {
                            $invalid = true;
                        } else {
                            $responseCollection = $responseCollection->merge($responses);
                        }
                    }

                    if (!$invalid) {
                        $submissionIds[] = $submission->id;

                        $submission->responses = $responseCollection;
                        $submissionCollection = $submissionCollection->push($submission);
                    }
                }
            }

            return $this->returnSuccessMessage('submissions', [
                "submission_ids" => $submissionIds,
                "submissions" => SubmissionResource::collection($submissionCollection)
            ]);
        } catch (Exception $e) {
            // Send error
            return $this->returnErrorMessage(503, $e->getMessage());
        }
    }

    /**
     * Filter submission and export as CSV
     *
     * @param  $application_slug
     * @param  Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function exportSubmission($application_slug, Request $request)
    {
        $this->validate($request, [
            'filters' => 'array',
            'filters.*.source' => 'required',
            'filters.*.query' => 'filled|integer',
            'filters.*.question_id' => 'filled|integer'
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

            // ToDo: check admin

            $filters = $request->input('filters');
            $comparisons = [];
            $questions = [];
            foreach ($filters as $key => $filter) {
                if ($filter['query']) {
                    $query = Comparator::find($filter['query']);
                    if ($query) {
                        $comparison = $this->getComparator($query->comparator, $filter['value']);
                        $comparison['source'] = $filter['source'];

                        if ($comparison['source'] == 'question_id') {
                            if ($filter['question_id']) {
                                $comparison['question_id'] = $filter['question_id'];
                                $questions[] = $comparison;
                            }
                        } else {
                            $comparisons[] = $comparison;
                        }
                    }
                }
            }

            $forms = $application->forms()->get();
            $submissionsData = [];
            foreach ($forms as $form) {
                $submissions = $form->submissions;
                foreach ($comparisons as $comparison) {
                    switch ($comparison['query']) {
                        case 'is null':
                            $submissions = $submissions->whereNull($comparison['source']);
                            break;
                        case 'is not null':
                            $submissions = $submissions->whereNotNull($comparison['source']);
                            break;
                        case 'in list':
                            $submissions = $submissions->whereIn($comparison['source'], explode(',', $comparison['value']));
                            break;
                        case 'not in list':
                            $submissions = $submissions->whereNotIn($comparison['source'], explode(',', $comparison['value']));
                            break;
                        case '':
                            break;
                        default:
                            $submissions = $submissions->where($comparison['source'], $comparison['query'], $comparison['value']);
                    }
                }

                $submissions = $submissions->all();
                foreach ($submissions as $submission) {
                    $invalid = false;
                    $responseCollection = new Collection();

                    foreach ($questions as $question) {
                        $responses = $submission->responses->where('question_id', $question['question_id']);
                        switch ($question['query']) {
                            case 'is null':
                                $responses = $responses->whereNull('response');
                                break;
                            case 'is not null':
                                $responses = $responses->whereNotNull('response');
                                break;
                            case 'in list':
                                $responses = $responses->whereIn('response', explode(',', $question['value']));
                                break;
                            case 'not in list':
                                $responses = $responses->whereNotIn('response', explode(',', $question['value']));
                                break;
                            case '':
                                break;
                            default:
                                $responses = $responses->where('response', $question['query'], $question['value']);
                        }

                        if (count($responses->all()) == 0) {
                            $invalid = true;
                        } else {
                            $responseCollection = $responseCollection->merge($responses);
                        }
                    }

                    if (!$invalid) {
                        // ToDo: Consider about the export response column
                        $submissionsData[] = [
                            'Submission ID' => $submission->id,
                            'Form ID' => $submission->form_id,
                            'User ID' => $submission->user_id,
                            'Team ID' => $submission->team_id,
                            'Progress' => $submission->progress,
                            'Period Start' => $submission->period_start,
                            'Period End' => $submission->period_end,
                            'Status' => Status::find($submission->status_id)->status
                        ];
                    }
                }
            }

            return Excel::create($application->name, function ($excel) use ($submissionsData) {
                $excel->sheet('Submissions', function ($sheet) use ($submissionsData) {
                    $sheet->fromArray($submissionsData);
                });
            })->download('xlsx');
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
