<?php

namespace App\Http\Controllers;

use Auth;
use Exception;
use App\Models\Application;
use App\Models\Form;
use App\Models\QuestionType;
use App\Http\Resources\FormResource;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class FormController extends Controller
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
		$application = Auth::user()->applications()->where('slug', $application_slug)->first();

		// Send error if application does not exist
		if (!$application) {
			return $this->returnApplicationNameError();
		}

		return $this->returnSuccessMessage('forms', FormResource::collection($application->forms()->get()));
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
			'name' => 'required|max:191'
		]);

		try {
			$application = Auth::user()->applications()->where('slug', $application_slug)->first();

			// Send error if application does not exist
			if (!$application) {
				return $this->returnApplicationNameError();
			}

			// Create form
			$form = $application->forms()->create($request->only('name'));

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
	 * Display the specified resource.
	 *
	 * @param  string $application_slug
	 * @param  int $id
	 *
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function show($application_slug, $id)
	{
		$application = Auth::user()->applications()->where('slug', $application_slug)->first();

		// Send error if application does not exist
		if (!$application) {
			return $this->returnApplicationNameError();
		}

		$form = $application->forms()->find($id);
		if ($form) {
			return $this->returnSuccessMessage('form', new FormResource($form));
		}

		// Send error if form does not exist
		return $this->returnError('form', 404, 'show');
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
			'name' => 'filled|max:191',
			'show_progress' => 'filled|boolean',
			'csv' => 'file'
		]);

		try {
			$application = Auth::user()->applications()->where('slug', $application_slug)->first();

			// Send error if application does not exist
			if (!$application) {
				return $this->returnApplicationNameError();
			}

			$form = $application->forms()->find($id);

			// Send error if form does not exist
			if (!$form) {
				return $this->returnError('form', 404, 'update');
			}

			// Update form
			if ($form->fill($request->only('name', 'show_progress'))->save()) {
				// Analyze CSV
				$this->analyzeCSV($form, $request);

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
	 * @param  string $application_slug
	 * @param  int $id
	 *
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function destroy($application_slug, $id)
	{
		try {
			$application = Auth::user()->applications()->where('slug', $application_slug)->first();

			// Send error if application does not exist
			if (!$application) {
				return $this->returnApplicationNameError();
			}

			$form = $application->forms()->find($id);

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
	 * Import data from CSV
	 *
	 * @param $form
	 * @param Request $request
	 *
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function analyzeCSV($form, Request $request)
	{
		try {
			if ($request->hasFile('csv') && $request->file('csv')->isValid()) {
				// Remove original data of form
				$form->sections->each(function ($section) {
					$section->delete();
				});
				$form->submissions->each(function ($submission) {
					$submission->delete();
				});
				$form->validations->each(function ($validation) {
					$validation->delete();
				});

				// Read data from csv file
				$path = $request->file('csv')->getRealPath();

				$data = Excel::load($path, function ($reader) {})->get();

				if (!empty($data) && $data->count()) {
					// If there is multiple sheets
					if (!array_key_exists('section_name', $data[0])) {
						$data = $data[0];
					}

					foreach ($data as $dt) {
						// Handling Section
						$created = false;
						$sections = $form->sections()->get();
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

							$section = $form->sections()->create([
								'name' => $dt->section_name,
								'parent_section_id' => $parent_section_id,
								'order' => $dt->section_order || 1,
								'repeatable' => $dt->section_repeatable || 0,
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
									'description' => $dt->question_description || null,
									'mandatory' => $dt->question_mandatory,
									'question_type_id' => $question_type_id,
									'order' => $dt->question_order || 1
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
										'parameter' => $dt->answer_parameter || true,
										'order' => $dt->answer_order || 1
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
			$application = Application::where('slug', $application_slug)->first();

			// Send error if application does not exist
			if (!$application) {
				return $this->returnApplicationNameError();
			}

			$form = $application->forms()->find($id);

			// Send error if form does not exist
			if (!$form) {
				return $this->returnError('form', 404, 'export');
			}

			$data = [];
            foreach ($form->sections as $section) {
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

			return Excel::create($form->name, function ($excel) use ($data) {
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
			'form_ids' => 'required|array',
			'form_ids.*' => 'integer'
		]);

		try {
			$application = Auth::user()->applications()->where('slug', $application_slug)->first();

			// Send error if application does not exist
			if (!$application) {
				return $this->returnApplicationNameError();
			}

			$form_ids = $request->input('form_ids', []);
			$forms = $application->forms()->get();
			foreach ($forms as $form) {
				$form->auto = in_array($form->id, $form_ids);
				$form->save();
			}

			return $this->returnSuccessMessage('message', 'Auto fields are updated successfully.');
		} catch (Exception $e) {
			// Send error
			return $this->returnErrorMessage(503, $e->getMessage());
		}
	}
}
