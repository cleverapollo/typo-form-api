<?php

namespace App\Http\Controllers;

use Auth;
use Exception;
use App\Models\Period;
use App\Models\QuestionType;
use App\Http\Resources\FormResource;
use App\Http\Resources\AnswerResource;
use App\Http\Resources\QuestionResource;
use Illuminate\Http\Request;

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
	 */
	public function store($application_slug, Request $request)
	{
		$this->validate($request, [
			'name' => 'required|max:191',
			'period_start' => 'nullable|date',
			'period_end' => 'nullable|date',
			'period_id' => 'nullable|integer|min:1',
			'show_progress' => 'required|boolean'
		]);

		try {
			$application = Auth::user()->applications()->where('slug', $application_slug)->first();

			// Send error if application does not exist
			if (!$application) {
				return $this->returnApplicationNameError();
			}

			if ($period_id = $request->input('period_id')) {
				// Send error if period does not exist
				if (!Period::find($period_id)) {
					return $this->returnError('period', 404, 'create form');
				}
			}

			// Create form
			$form = $application->forms()->create($request->only('name', 'period_start', 'period_end', 'period_id', 'show_progress'));

			if ($form) {
				return $this->returnSuccessMessage('form', new FormResource($form));
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
	 */
	public function update($application_slug, $id, Request $request)
	{
		$this->validate($request, [
			'name' => 'filled|max:191',
			'period_start' => 'nullable|date',
			'period_end' => 'nullable|date',
			'period_id' => 'nullable|integer|min:1',
			'show_progress' => 'filled|boolean'
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

			if ($period_id = $request->input('period_id')) {
				// Send error if period does not exist
				if (!Period::find($period_id)) {
					return $this->returnError('period', 404, 'create form');
				}
			}

			// Update form
			if ($form->fill($request->only('name', 'period_start', 'period_end', 'period_id', 'show_progress'))->save()) {
				return $this->returnSuccessMessage('form', new FormResource($form));
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

			// Send error if there is an error on update
			return $this->returnError('form', 503, 'delete');
		} catch (Exception $e) {
			// Send error
			return $this->returnErrorMessage(503, $e->getMessage());
		}
	}

	/**
	 * Create form from CSV
	 *
	 * @param  string $application_slug
	 * @param  Request $request
	 *
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function createFromCSV($application_slug, Request $request)
	{
		$this->validate($request, [
			'name' => 'required|max:191',
			'period_start' => 'nullable|date',
			'period_end' => 'nullable|date',
			'period_id' => 'nullable|integer|min:1',
			'show_progress' => 'required|boolean',
			'sections' => 'array',
			'sections.*.name' => 'required|max:191',
			'sections.*.parent_section_id' => 'nullable|integer|min:1',
			'sections.*.questions' => 'array',
			'sections.*.questions.*.question' => 'required',
			'sections.*.questions.*.mandatory' => 'required|boolean',
			'sections.*.questions.*.question_type_id' => 'required|integer|min:1',
			'sections.*.questions.*.answers' => 'array',
			'sections.*.questions.*.answers.*.parameter' => 'required|boolean'
		]);

		try {
			$application = Auth::user()->applications()->where('slug', $application_slug)->first();

			// Send error if application does not exist
			if (!$application) {
				return $this->returnApplicationNameError();
			}

			if ($period_id = $request->input('period_id')) {
				// Send error if period does not exist
				if (!Period::find($period_id)) {
					return $this->returnError('period', 404, 'create form');
				}
			}

			// Create form
			$form = $application->forms()->create($request->only('name', 'period_start', 'period_end', 'period_id', 'show_progress'));

			if ($form) {
				$sections = $request->input('sections', []);

				// Create sections
				$createdSections = [];
				foreach ($sections as $section) {
					// Count order
					$sOrder = 1;
					if (!$section['parent_section_id']) {
						if (count($form->sections) > 0) {
							$sOrder = $form->sections()->where('parent_section_id', null)->max('order') + 1;
						}
					} else {
						$parent_section = $form->sections()->find($section['parent_section_id']);

						// Send error if parent section does not exist
						if (!$parent_section) {
							continue;
							// return $this->returnError('parent section', 404, 'create section');
						}

						if (count($parent_section->children) > 0) {
							$sOrder = $parent_section->children()->max('order') + 1;
						}

						if (count($parent_section->questions) > 0) {
							$sOrder = max($sOrder, $parent_section->questions()->max('order') + 1);
						}
					}

					$createdSection = $form->sections()->create([
						'name' => $section['name'],
						'parent_section_id' => $section['parent_section_id'],
						'order' => $sOrder
					]);

					if ($createdSection) {
						$questions = $section['questions'];
						if ($questions && count($questions) > 0) {
							// Create questions
							$createdQuestions = [];
							foreach ($questions as $question) {
								// Check whether the question type exists or not
								if (!QuestionType::find($question['question_type_id'])) {
									continue;
									// return $this->returnError('question type', 404, 'create question');
								}

								// Count order
								$qOrder = 1;
								if (count($createdSection->questions) > 0) {
									$qOrder = $createdSection->questions()->max('order') + 1;
								}
								if (count($createdSection->children) > 0) {
									$qOrder = max($qOrder, $createdSection->children()->max('order') + 1);
								}

								$createdQuestion = $createdSection->questions()->create([
									'question' => $question['question'],
									'description' => $question['description'],
									'mandatory' => $question['mandatory'],
									'question_type_id' => $question['question_type_id'],
									'order' => $qOrder
								]);

								if ($createdQuestion) {
									$answers = $question['answers'];
									if ($answers && count($answers) > 0) {
										$createdAnswers = [];
										foreach ($answers as $answer) {
											// Count order
											$aOrder = 1;
											if (count($createdQuestion->answers) > 0) {
												$aOrder = $createdQuestion->answers()->max('order') + 1;
											}

											$createdAnswer = $createdQuestion->answers()->create([
												'answer' => $answer['answer'],
												'parameter' => $answer['parameter'],
												'order' => $aOrder
											]);

											if ($createdAnswer) {
												// Push the created answer to return array
												array_push($createdAnswers, $createdAnswer);
											}
										}

										// Push the created answers to parent question
										$createdQuestion['answers'] = AnswerResource::collection($createdAnswers);
									}

									// Push the created question to return array
									array_push($createdQuestions, $createdQuestion);
								}
							}

							// Push the created questions to parent section
							$createdSection['questions'] = QuestionResource::collection($createdQuestions);
						}

						// Push the created section to return array
						array_push($createdSections, $createdSection);
					}
				}

				return $this->returnSuccessMessage('form', new FormResource($form));
			}

			// Send error if form is not created
			return $this->returnError('form', 503, 'create');
		} catch (Exception $e) {
			// Send error
			return $this->returnErrorMessage(503, $e->getMessage());
		}
	}
}
