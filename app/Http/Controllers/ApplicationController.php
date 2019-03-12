<?php

namespace App\Http\Controllers;

use Auth;
use Exception;
use Illuminate\Support\Facades\Storage;
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
use App\Http\Resources\FormResource;
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
            $this->acceptInvitation('organisation');

            $origin = $request->header('Origin');
            if (strlen($origin)) {
                $request_slug = explode('.', explode('://', $origin)[1])[0];
                $request_application = Application::where('slug', $request_slug)->first();
                if ($request_application && $request_application->join_flag) {
                    $application_user = ApplicationUser::where([
                        'user_id' => $user->id,
                        'application_id' => $request_application->id
                    ])->first();

                    if (!$application_user) {
                        ApplicationUser::create([
                            'user_id' => $user->id,
                            'application_id' => $request_application->id,
                            'role_id' => Role::where('name', 'User')->first()->id
                        ]);
                    }
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
				'subject' => 'Create form',
				'body' => 'Form is created successfully. Please fill out the form template and send form.',
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

			if ($application->fill($request->only('name', 'css', 'icon', 'logo', 'background_image', 'support_text', 'join_flag', 'default_route'))->save()) {
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
			$application->load(['users', 'organisations', 'form_templates.forms.status', 'form_templates.sections.questions.answers', 'form_templates.sections.questions.responses']);
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
     * Filter forms
     *
     * @param  $application_slug
     * @param  Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function filterForm($application_slug, Request $request)
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
                        } else if ($comparison['source'] == 'Form Template' || $comparison['source'] == 'User' || $comparison['source'] == 'Organisation' || $comparison['source'] == 'Status') {
                            $names[] = $comparison;
                        } else {
                        	if ($comparison['source'] == 'ID') {
                        		$comparison['source'] = 'id';
                        	} else if ($comparison['source'] == 'Form Template ID') {
                        		$comparison['source'] = 'form_template_id';
                        	} else if ($comparison['source'] == 'User ID') {
                        		$comparison['source'] = 'user_id';
                        	} else if ($comparison['source'] == 'Organisation ID') {
                        		$comparison['source'] = 'organisation_id';
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

            $form_templates = $application->form_templates()->get();
            $formIds = [];
            $formCollection = new Collection();
            foreach ($form_templates as $form_template) {
                $forms = $form_template->forms;
                foreach ($comparisons as $comparison) {
                    switch ($comparison['query']) {
                        case 'is null':
                            $forms = $forms->filter(function ($item) use ($comparison) {
								return $item[$comparison['source']] === null;
							});
                            break;
                        case 'is not null':
                            $forms = $forms->filter(function ($item) use ($comparison) {
								return $item[$comparison['source']] !== null;
							});
                            break;
                        case 'in list':
                            $forms = $forms->filter(function ($item) use ($comparison) {
								return in_array($item[$comparison['source']], explode(',', $comparison['value']));
							});
                            break;
                        case 'not in list':
                            $forms = $forms->filter(function ($item) use ($comparison) {
								return !in_array($item[$comparison['source']], explode(',', $comparison['value']));
							});
                            break;
                        case '=':
                            $forms = $forms->filter(function ($item) use ($comparison) {
								return $item[$comparison['source']] == $comparison['value'];
							});
                            break;
                        case '!=':
                            $forms = $forms->filter(function ($item) use ($comparison) {
								return $item[$comparison['source']] != $comparison['value'];
							});
                            break;
                        case '<':
                            $forms = $forms->filter(function ($item) use ($comparison) {
								return $item[$comparison['source']] < $comparison['value'];
							});
                            break;
                        case '>':
                            $forms = $forms->filter(function ($item) use ($comparison) {
								return $item[$comparison['source']] > $comparison['value'];
							});
                            break;
                        case '<=':
                            $forms = $forms->filter(function ($item) use ($comparison) {
								return $item[$comparison['source']] <= $comparison['value'];
							});
                            break;
                        case '>=':
                            $forms = $forms->filter(function ($item) use ($comparison) {
								return $item[$comparison['source']] >= $comparison['value'];
							});
                            break;
                        default:
                            break;
                    }
                }

                $forms = $forms->all();

                foreach ($forms as $form) {
                    $invalid = false;
                    $responseCollection = new Collection();

                    foreach ($names as $name) {
                    	$name_str = '';

                    	if ($name['source'] == 'Form Template') {
                    		$name_str = $form->form_template->name;
                    	} else if ($name['source'] == 'Organisation') {
                    		$name_str = $form->organisation ? $form->organisation->name : null;
                    	} else if ($name['source'] == 'User') {
                    		$name_str = $form->user->first_name + ' ' + $form->name->last_name;
                    	} else if ($name['source'] == 'Status') {
                    		$name_str = $form->status->status;
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
                        $responses = $form->responses->where('question_id', $question['question_id']);
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
                        $formIds[] = $form->id;

                        $form->responses = $responseCollection;
                        $formCollection = $formCollection->push($form);
                    }
                }
            }

            return $this->returnSuccessMessage('forms', [
                "form_ids" => $formIds,
                "forms" => FormResource::collection($formCollection)
            ]);
        } catch (Exception $e) {
            // Send error
            return $this->returnErrorMessage(503, $e->getMessage());
        }
    }

    /**
     * Filter forms and export as CSV
     *
     * @param  $application_slug
     * @param  Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function exportForm($application_slug, Request $request)
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

            $form_templates = $application->form_templates()->get();
            $formsData = [];
            foreach ($form_templates as $form_template) {
                $forms = $form_template->forms;
                foreach ($comparisons as $comparison) {
                    switch ($comparison['query']) {
                        case 'is null':
                            $forms = $forms->whereNull($comparison['source']);
                            break;
                        case 'is not null':
                            $forms = $forms->whereNotNull($comparison['source']);
                            break;
                        case 'in list':
                            $forms = $forms->whereIn($comparison['source'], explode(',', $comparison['value']));
                            break;
                        case 'not in list':
                            $forms = $forms->whereNotIn($comparison['source'], explode(',', $comparison['value']));
                            break;
                        case '':
                            break;
                        default:
                            $forms = $forms->where($comparison['source'], $comparison['query'], $comparison['value']);
                    }
                }

                $forms = $forms->all();
                foreach ($forms as $form) {
                    $invalid = false;
                    $responseCollection = new Collection();

                    foreach ($questions as $question) {
                        $responses = $form->responses->where('question_id', $question['question_id']);
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
                        $formsData[] = [
                            'Form ID' => $form->id,
                            'Form Template ID' => $form->form_template_id,
                            'User ID' => $form->user_id,
                            'Organisation ID' => $form->organisation_id,
                            'Progress' => $form->progress,
                            'Period Start' => $form->period_start,
                            'Period End' => $form->period_end,
                            'Status' => Status::find($form->status_id)->status
                        ];
                    }
                }
            }

            return Excel::create($application->name, function ($excel) use ($formsData) {
                $excel->sheet('Forms', function ($sheet) use ($formsData) {
                    $sheet->fromArray($formsData);
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
