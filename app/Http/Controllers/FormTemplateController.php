<?php

namespace App\Http\Controllers;

use Auth;
use Exception;
use App\User;
use App\Models\Application;
use App\Models\ApplicationUser;
use App\Models\Organisation;
use App\Models\FormTemplate;
use App\Models\QuestionType;
use App\Http\Resources\FormTemplateResource;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class FormTemplateController extends Controller
{
	/**
	 * Create a new controller instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		$this->middleware('auth:api', ['except' => []]);
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
		$user = Auth::user();

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
            'user_id' => 'nullable|integer|min:1',
            'organisation_id' => 'nullable|integer|min:1',
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

			// Create form_template
            $form_template = $application->form_templates()->create([
                'user_id' => $user_id,
                'organisation_id' => $organisation_id,
                'name' => $request->input('name')
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
            'user_id' => 'nullable|integer|min:1',
            'organisation_id' => 'nullable|integer|min:1',
			'name' => 'filled|max:191',
			'show_progress' => 'filled|boolean',
			'csv' => 'file'
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
				return $this->returnError('application', 403, 'update form_templates');
			}

			// Send error if application does not exist
			if (!$application) {
				return $this->returnApplicationNameError();
			}

			$form_template = $application->form_templates()->find($id);

			// Send error if form_template does not exist
			if (!$form_template) {
				return $this->returnError('form_template', 404, 'update');
			}

			// Update form_template
			if ($form_template->fill($request->only('user_id', 'organisation_id', 'name', 'show_progress'))->save()) {
				// Analyze CSV
				$this->analyzeCSV($form_template, $request);

				return $this->returnSuccessMessage('form_template', new FormTemplateResource(FormTemplate::find($form_template->id)));
			}

			// Send error if there is an error on update
			return $this->returnError('form_template', 503, 'update');
		} catch (Exception $e) {
			// Send error
			return $this->returnErrorMessage(503, $e->getMessage());
		}
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
	 * Import data from CSV
	 *
	 * @param $form_template
	 * @param Request $request
	 *
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function analyzeCSV($form_template, Request $request)
	{
		try {
			if ($request->hasFile('csv') && $request->file('csv')->isValid()) {
				// Remove original data of form_template
				$form_template->sections->each(function ($section) {
					$section->delete();
				});
				$form_template->forms->each(function ($form) {
                    $form->delete();
				});
				$form_template->validations->each(function ($validation) {
					$validation->delete();
				});

				// Read data from csv file
				$path = $request->file('csv')->getRealPath();

				$data = Excel::load($path, function ($reader) {})->get();

				if (!empty($data) && $data->count()) {
					// If there is multiple sheets
					/* if (!array_key_exists('section_name', $data[0])) {
						$data = $data[0];
					} */

					foreach ($data as $dt) {
						// Handling Section
						$created = false;
						$sections = $form_template->sections()->get();
						foreach ($sections as $s) {
							if ($s->name == $dt->section_name) {
								$created = true;
								$section = $s;
							}
						}

						// Create section if not created
						if (!$created) {
							$parent_section_id = null;
							if ($dt->parent_section_name) {
								foreach ($sections as $s) {
									if ($s->name == $dt->parent_section_name) {
										$parent_section_id = $s->id;
									}
								}
							}

							$section = $form_template->sections()->create([
								'name' => $dt->section_name,
								'parent_section_id' => $parent_section_id,
								'order' => ($dt->section_order ? $dt->section_order : 1),
								'repeatable' => ($dt->section_repeatable ? $dt->section_repeatable : 0),
								'max_rows' => $dt->section_repeatable_rows_max_count,
								'min_rows' => $dt->section_repeatable_rows_min_count
							]);
						}

						if ($dt->question) {
							// Handling Question
							$created = false;
							$questions = $section->questions()->get();
							foreach ($questions as $q) {
								if ($q->question == $dt->question && $q->order == $dt->question_order) {
									$created = true;
									$question = $q;
								}
							}

							// Create question if not created
							if (!$created) {
								$question_type_id = null;
								$question_type = QuestionType::where('type', $dt->question_type)->first();
								if ($question_type) {
									$question_type_id = $question_type->id;
								}

								$question = $section->questions()->create([
									'question' => $dt->question,
									'description' => ($dt->question_description ? $dt->question_description : ''),
									'mandatory' => $dt->question_mandatory,
									'question_type_id' => $question_type_id,
									'order' => ($dt->question_order ? $dt->question_order : 1)
								]);
							}

							if ($dt->answer) {
								// Handling Answer
								$created = false;
								$answers = $question->answers()->get();
								foreach ($answers as $a) {
									if ($a->answer == $dt->answer && $a->order == $dt->answer_order) {
										$created = true;
									}
								}

								// Create answer if not created
								if (!$created) {
									$question->answers()->create([
										'answer' => $dt->answer,
										'parameter' => ($dt->answer_parameter ? $dt->answer_parameter : true),
										'order' => ($dt->answer_order ? $dt->answer_order : 1)
									]);
								}
							}
						}
					}
				}
			}
		} catch (Exception $e) {
			// Send error
			return $this->returnErrorMessage(503, 'Invalid CSV file.');
		}
	}

	/**
	 * Export data to CSV
	 *
	 * @param $application_slug
	 * @param $id
	 *
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function exportCSV($application_slug, $id)
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

			$form_template = $application->form_templates()->find($id);

			// Send error if form_template does not exist
			if (!$form_template) {
				return $this->returnError('form_template', 404, 'export');
			}

			$data = [];
            foreach ($form_template->sections as $section) {
                if (count($section->questions) > 0) {
                    foreach ($section->questions as $question) {
                        if (count($question->answers) > 0) {
                            foreach ($question->answers as $answer) {
                                $data[] = [
                                    'Section ID' => $section->id,
                                    'Section Name' => $section->name,
                                    'Parent Section Name' => $section->parent_section_id ? $section->parent->name : '',
                                    'Section Order' => $section->order,
                                    'Section Repeatable' => (bool)($section->repeatable),
                                    'Section Repeatable Rows Min Count' => $section->min_rows,
                                    'Section Repeatable Rows Max Count' => $section->max_rows,
                                    'Question ID' => $question->id,
                                    'Question' => $question->question,
                                    'Question Description' => $question->description,
                                    'Question Order' => $question->order,
                                    'Question Mandatory' => $question->mandatory,
                                    'Question Type' => QuestionType::find($question->question_type_id)->type,
                                    'Answer ID' => $answer->id,
                                    'Answer' => $answer->answer,
                                    'Answer Parameter' => $answer->parameter ? 'TRUE' : 'FALSE',
                                    'Answer Order' => $answer->order
                                ];
                            }
                        } else {
                            $data[] = [
                                'Section ID' => $section->id,
                                'Section Name' => $section->name,
                                'Parent Section Name' => $section->parent_section_id ? $section->parent->name : '',
                                'Section Order' => $section->order,
                                'Section Repeatable' => (bool)($section->repeatable),
                                'Section Repeatable Rows Min Count' => $section->min_rows,
                                'Section Repeatable Rows Max Count' => $section->max_rows,
                                'Question ID' => $question->id,
                                'Question' => $question->question,
                                'Question Description' => $question->description,
                                'Question Order' => $question->order,
                                'Question Mandatory' => $question->mandatory,
                                'Question Type' => QuestionType::find($question->question_type_id)->type,
                                'Answer ID' => '',
                                'Answer' => '',
                                'Answer Parameter' => '',
                                'Answer Order' => ''
                            ];
                        }
                    }
                } else {
                    $data[] = [
                        'Section ID' => $section->id,
                        'Section Name' => $section->name,
                        'Parent Section Name' => $section->parent_section_id ? $section->parent->name : '',
                        'Section Order' => $section->order,
                        'Section Repeatable' => (bool)($section->repeatable),
                        'Section Repeatable Rows Min Count' => $section->min_rows,
                        'Section Repeatable Rows Max Count' => $section->max_rows,
                        'Question ID' => '',
                        'Question' => '',
                        'Question Description' => '',
                        'Question Order' => '',
                        'Question Mandatory' => '',
                        'Question Type' => '',
                        'Answer ID' => '',
                        'Answer' => '',
                        'Answer Parameter' => '',
                        'Answer Order' => ''
                    ];
                }
            }

			return Excel::create($form_template->name, function ($excel) use ($data) {
				$excel->sheet('Sheet 1', function ($sheet) use ($data) {
					$sheet->fromArray($data);
				});
			})->download('xlsx');
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
