<?php

namespace App\Http\Controllers;

use Auth;
use Exception;
use Storage;
use App\Models\Role;
use App\Models\Status;
use App\Models\Application;
use App\Models\ApplicationUser;
use App\Models\ApplicationInvitation;
use App\Models\Comparator;
use App\Models\QuestionType;
use App\Http\Resources\UserResource;
use App\Http\Resources\ApplicationResource;
use App\Http\Resources\ApplicationUserResource;
use App\Http\Resources\ApplicationInvitationResource;
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
		$this->middleware('auth:api');
	}

	/**
	 * Display a listing of the resource.
	 *
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function index()
	{
		$user = Auth::user();
		if ($user->role->name == 'Super Admin') {
			$applications = Application::get();
		} else {
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
		if ($user) {
			if ($user->role->name == 'Super Admin') {
				$application = Application::where('slug', $application_slug)->first();
			} else {
                $application = $user->applications()->where('slug', $application_slug)->first();
            }
		} else {
            $application = Application::where('slug', $application_slug)->first();
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

			if ($application->fill($request->only('name', 'css', 'icon'))->save()) {
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
	 * Accept invitation request.
	 *
	 * @param  string $token
	 *
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function invitation($token)
	{
		return $this->acceptInvitation('application', $token);
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

		$invitedUsers = ApplicationInvitation::where([
			'application_id' => $application->id,
			'status' => 0
		])->get();

		return $this->returnSuccessMessage('users', [
			'current' => UserResource::collection($currentUsers),
			'unaccepted' => ApplicationInvitationResource::collection($invitedUsers)
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

		// Send invitation
		$this->sendInvitation('application', $application, $invitations);

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

			$application_invitation = ApplicationInvitation::where([
				'id' => $id,
				'application_id' => $application->id
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
				return $this->returnSuccessMessage('user', new ApplicationInvitationResource(ApplicationInvitation::find($application_invitation->id)));
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

			$application_invitation = ApplicationInvitation::where([
				'id' => $id,
				'application_id' => $application->id
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
			$data = [];
			$data['Applications'][$application->id] = $application->toArray();

			//Users
			foreach($application->users as $user) {
				$user_details = array_intersect_key($user->toArray(), array_flip(['id', 'first_name', 'last_name', 'email']));
				$application_user_details = array_intersect_key($user->pivot->toArray(), array_flip(['role_id', 'created_at', 'updated_at']));
				$data['Users'][$user->id] = array_merge($user_details, $application_user_details);
			}

			//Teams
			foreach($application->teams as $team) {
				$data['Teams'][$team->id] = $team->toArray();
			}

			//Forms
			foreach($application->forms as $form) {
				$data['Forms'][$form->id] = $form->toArray();

				//Sections
				foreach($form->sections as $section) {
					$data['Sections'][$section->id] = $section->toArray();
				
					//Questions
					foreach($section->questions as $question) {
						$data['Questions'][$question->id] = $question->toArray();

						//Answers
						foreach($question->answers as $answer) {
							$data['Answers'][$answer->id] = $answer->toArray();
						}

						//Responses
						foreach($question->responses as $response) {
							$data['Responses'][$response->id] = $response->toArray();
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
