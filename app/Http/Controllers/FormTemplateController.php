<?php

namespace App\Http\Controllers;

use Auth;
use Exception;
use App\Models\Application;
use App\Models\ApplicationUser;
use App\Models\FormTemplate;
use App\Models\QuestionType;
use App\Models\Type;
use App\Models\Status;
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
                            'sort_id' => $question->sort_id
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
			if ($form_template->fill($request->only('type_id', 'name', 'show_progress', 'status_id'))->save()) {
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
	 * Update the form template
	 *
	 * @param  string $application_slug
	 * @param  int $id
	 * @param  \Illuminate\Http\Request $request
	 *
	 * @return \Illuminate\Http\JsonResponse
	 * @throws \Illuminate\Validation\ValidationException
	 */
	public function uploadFormTemplate($application_slug, $id, Request $request)
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

			return $this->analyzeCSV($form_template, $request);

			// Send error if there is an error on update
			return $this->returnError('upload', 503, 'update');
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
			ini_set('max_execution_time', 0);
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
                                'order' => $dt->section_order ?? 1,
                                'repeatable' => $dt->section_repeatable ?? 0,
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
                                    'description' => $dt->question_description ?? '',
                                    'mandatory' => $dt->question_mandatory ?? 1,
                                    'question_type_id' => $question_type_id ?? 1,
                                    'order' => $dt->question_order ?? 1
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
                                        'parameter' => $dt->answer_parameter ?? true,
                                        'order' => $dt->answer_order ?? 1
									]);
								}
							}
						}
					}
					return $this->returnSuccessMessage('upload', 'Form template has been uploaded successfully.');
				}
            } else {
                return $this->returnErrorMessage(503, 'Invalid CSV file.');
			}
		} catch (Exception $e) {
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
