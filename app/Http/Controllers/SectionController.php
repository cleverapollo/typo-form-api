<?php

namespace App\Http\Controllers;

use Auth;
use Exception;
use App\Models\Form;
use App\Models\Section;
use App\Models\Question;
use App\Models\Answer;
use App\Http\Resources\SectionResource;
use Illuminate\Http\Request;

class SectionController extends Controller
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
		$sections = Form::find($form_id)->sections()->get();

		return $this->returnSuccessMessage('sections', SectionResource::collection($sections));
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @param  int $form_id
	 * @param  \Illuminate\Http\Request $request
	 *
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function store($form_id, Request $request)
	{
		$this->validate($request, [
			'name' => 'required|max:191',
			'order' => 'filled|integer|min:0',
			'section_id' => 'nullable|integer|min:1'
		]);

		try {
			$form = Form::find($form_id);

			// Send error if form does not exist
			if (!$form) {
				return $this->returnError('form', 404, 'create section');
			}

			$order = $request->input('order');
			if (!$order) {
				if (Section::where('form_id', $form_id)->exists()) {
					$order = Section::where('form_id', $form_id)->max('order') + 1;
				} else {
					$order = 0;
				}
			}

			// Create section
			$section = $form->sections()->create([
				'name' => $request->input('name'),
				'section_id' => $request->input('section_id'),
				'order' => $order
			]);

			if ($section) {
				return $this->returnSuccessMessage('section', new SectionResource($section));
			}

			// Send error if section is not created
			return $this->returnError('section', 503, 'create');
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

			// Send error if form does not exist
			if (!$form) {
				return $this->returnError('form', 404, 'duplicate section');
			}

			$section = $form->sections()->find($id);

			// Send error if section does not exist
			if (!$section) {
				return $this->returnError('section', 404, 'duplicate');
			}

			// Duplicate section
			$newSection = $form->sections()->create([
				'name' => $section->name,
				'section_id' => $section->section_id,
				'order' => ($section->order + 1)
			]);

			if ($newSection) {
				// Update other sections order
				$form->sections()->where('order', '>=', $newSection->order)->get()->each(function ($other) {
					$other->order += 1;
					$other->save();
				});

				return $this->returnSuccessMessage('section', new SectionResource($newSection));
			}

			// Send error if section is not duplicated
			return $this->returnError('section', 503, 'duplicate');
		} catch (Exception $e) {
			// Send error
			return $this->returnErrorMessage(503, $e->getMessage());
		}
	}

	/**
	 * Store resources in storage.
	 *
	 * @param  int $form_id
	 * @param  \Illuminate\Http\Request $request
	 *
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function storeSections($form_id, Request $request)
	{
		$this->validate($request, [
			'sections' => 'array'
		]);

		$sections = $request->input('sections', []);
		if ($sections && count($sections) > 0) {
			foreach ($sections as $section) {
				$this->validate($section, [
					'name' => 'required|max:191',
					'order' => 'filled|integer|min:0',
					'section_id' => 'nullable|integer|min:1',
					'questions' => 'array'
				]);

				$questions = $section['questions'];
				if ($questions && count($questions) > 0) {
					foreach ($questions as $question) {
						$this->validate($question, [
							'question' => 'required',
							'description' => 'required',
							'mandatory' => 'required|boolean',
							'question_type_id' => 'required|integer|min:1',
							'order' => 'filled|integer|min:0',
							'answers' => 'array'
						]);

						$answers = $question['answers'];
						if ($answers && count($answers) > 0) {
							foreach ($answers as $answer) {
								$this->validate($answer, [
									'answer' => 'required',
									'parameter' => 'nullable|boolean',
									'order' => 'filled|integer|min:0'
								]);
							}
						}
					}
				}
			}
		}

		try {
			$form = Form::find($form_id);

			// Send error if form does not exist
			if (!$form) {
				return $this->returnError('form', 404, 'create section');
			}

			// Create sections
			$createdSections = [];
			foreach ($sections as $section) {
				$order = $section['order'];
				if (!$order) {
					if (Section::where('form_id', $form_id)->exists()) {
						$order = Section::where('form_id', $form_id)->max('order') + 1;
					} else {
						$order = 0;
					}
				}

				$createdSection = $form->sections()->create([
					'name' => $section['name'],
					'order' => $order,
					'section_id' => $section['section_id']
				]);

				if ($createdSection) {
					$questions = $section['questions'];
					if ($questions && count($questions) > 0) {
						// Create questions
						$createdQuestions = [];
						foreach ($questions as $question) {
							$order = $question['order'];
							if (!$order) {
								if (Question::where('section_id', $createdSection->id)->exists()) {
									$order = Question::where('section_id', $createdSection->id)->max('order') + 1;
								} else {
									$order = 0;
								}
							}

							$createdQuestion = $createdSection->questions()->create([
								'question' => $question['question'],
								'description' => $question['description'],
								'mandatory' => $question['mandatory'],
								'question_type_id' => $question['question_type_id'],
								'order' => $order
							]);

							if ($createdQuestion) {
								$answers = $question['answers'];
								if ($answers && count($answers) > 0) {
									$createdAnswers = [];
									foreach ($answers as $answer) {
										$order = $answer['order'];
										if (!$order) {
											if (Answer::where('question_id', $createdQuestion->id)->exists()) {
												$order = Answer::where('question_id', $createdQuestion->id)->max('order') + 1;
											} else {
												$order = 0;
											}
										}

										$createdAnswer = $createdQuestion->answers()->create([
											'answer' => $answer['answer'],
											'parameter' => $answer['parameter'],
											'order' => $order
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

			return $this->returnSuccessMessage('sections', SectionResource::collection($createdSections));
		} catch (Exception $e) {
			// Send error if section is not created
			return $this->returnError('sections', 503, 'create');
			// Send error
			// return $this->returnErrorMessage(503, $e->getMessage());
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
			return $this->returnError('form', 404, 'show section');
		}

		$section = $form->sections()->find($id);
		if ($section) {
			return $this->returnSuccessMessage('section', new SectionResource($section));
		}

		// Send error if section does not exist
		return $this->returnError('section', 404, 'show');
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  int $form_id
	 * @param  int $id
	 * @param  \Illuminate\Http\Request $request
	 *
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function update($form_id, $id, Request $request)
	{
		$this->validate($request, [
			'name' => 'filled|max:191',
			'order' => 'filled|integer|min:0',
			'section_id' => 'nullable|integer|min:1'
		]);

		try {
			$form = Form::find($form_id);

			// Send error if form does not exist
			if (!$form) {
				return $this->returnError('form', 404, 'update section');
			}

			$section = $form->sections()->find($id);

			// Send error if section does not exist
			if (!$section) {
				return $this->returnError('section', 404, 'update');
			}

			// Update section
			if ($section->fill($request->only('name', 'section_id', 'order'))->save()) {
				return $this->returnSuccessMessage('section', new SectionResource($section));
			}

			// Send error if there is an error on update
			return $this->returnError('section', 503, 'update');
		} catch (Exception $e) {
			// Send error
			return $this->returnErrorMessage(503, $e->getMessage());
		}
	}

	/**
	 * Update resources in storage.
	 *
	 * @param  int $form_id
	 * @param  \Illuminate\Http\Request $request
	 *
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function updateSections($form_id, Request $request)
	{
		$this->validate($request, [
			'sections' => 'array'
		]);

		$sections = $request->input('sections', []);
		if ($sections && count($sections) > 0) {
			foreach ($sections as $section) {
				$this->validate($section, [
					'id' => 'filled|integer|min:1',
					'name' => 'filled|max:191',
					'order' => 'filled|integer|min:0',
					'section_id' => 'nullable|integer|min:1',
					'questions' => 'array'
				]);

				$questions = $section['questions'];
				if ($questions && count($questions) > 0) {
					foreach ($questions as $question) {
						$this->validate($question, [
							'id' => 'filled|integer|min:1',
							'question' => 'filled',
							'description' => 'filled',
							'mandatory' => 'filled|boolean',
							'question_type_id' => 'filled|integer|min:1',
							'order' => 'filled|integer|min:0',
							'answers' => 'array'
						]);

						$answers = $question['answers'];
						if ($answers && count($answers) > 0) {
							foreach ($answers as $answer) {
								$this->validate($answer, [
									'id' => 'filled|integer|min:1',
									'answer' => 'filled',
									'parameter' => 'nullable|boolean',
									'order' => 'filled|integer|min:0'
								]);
							}
						}
					}
				}
			}
		}

		try {
			$form = Form::find($form_id);

			// Send error if form does not exist
			if (!$form) {
				return $this->returnError('form', 404, 'update section');
			}

			// Update sections
			$updatedSections = [];
			foreach ($sections as $section) {
				if ($section['id'] && ($updatedSection = $form->sections()->where('id', $section['id'])->first())) {
					$updatedSection->fill([
						'name' => $section['name'],
						'order' => $section['order'],
						'section_id' => $section['section_id']
					])->save();
				} else {
					$updatedSection = $form->sections()->create([
						'name' => $section['name'],
						'order' => $section['order'],
						'section_id' => $section['section_id']
					]);
				}

				if ($updatedSection) {
					$questions = $section['questions'];
					if ($questions && count($questions) > 0) {
						// Create questions
						$updatedQuestions = [];
						foreach ($questions as $question) {
							if ($question['id'] && ($updatedQuestion = $updatedSection->questions()->where('id', $question['id'])->first())) {
								$updatedQuestion->fill([
									'question' => $question['question'],
									'description' => $question['description'],
									'mandatory' => $question['mandatory'],
									'question_type_id' => $question['question_type_id'],
									'order' => $question['order']
								])->save();
							} else {
								$updatedQuestion = $updatedSection->questions()->create([
									'question' => $question['question'],
									'description' => $question['description'],
									'mandatory' => $question['mandatory'],
									'question_type_id' => $question['question_type_id'],
									'order' => $question['order']
								]);
							}

							if ($updatedQuestion) {
								$answers = $question['answers'];
								if ($answers && count($answers) > 0) {
									$updatedAnswers = [];
									foreach ($answers as $answer) {
										if ($answer['id'] && ($updatedAnswer = $updatedQuestion->answers()->where('id', $answer['id'])->first())) {
											$updatedAnswer->fill([
												'answer' => $answer['answer'],
												'parameter' => $answer['parameter'],
												'order' => $answer['order']
											])->save();
										} else {
											$updatedAnswer = $updatedQuestion->answers()->create([
												'answer' => $answer['answer'],
												'parameter' => $answer['parameter'],
												'order' => $answer['order']
											]);
										}

										if ($updatedAnswer) {
											// Push the updated answer to return array
											array_push($updatedAnswers, $updatedAnswer);
										}
									}

									// Push the updated answers to parent question
									$updatedQuestion['answers'] = AnswerResource::collection($updatedAnswers);
								}

								// Push the updated question to return array
								array_push($updatedQuestions, $updatedQuestion);
							}
						}

						// Push the updated questions to parent section
						$updatedSection['questions'] = QuestionResource::collection($updatedQuestions);
					}

					// Push the updated section to return array
					array_push($updatedSections, $updatedSection);
				}
			}

			return $this->returnSuccessMessage('sections', SectionResource::collection($updatedSections));
		} catch (Exception $e) {
			// Send error if section is not created
			return $this->returnError('sections', 503, 'update');
			// Send error
			// return $this->returnErrorMessage(503, $e->getMessage());
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

			// Send error if form does not exist
			if (!$form) {
				return $this->returnError('form', 404, 'delete section');
			}

			$section = $form->sections()->find($id);

			// Send error if section does not exist
			if (!$section) {
				return $this->returnError('section', 404, 'delete');
			}

			if ($section->delete()) {
				return $this->returnSuccessMessage('message', 'Section has been deleted successfully.');
			}

			// Send error if there is an error on update
			return $this->returnError('section', 503, 'delete');
		} catch (Exception $e) {
			// Send error
			return $this->returnErrorMessage(503, $e->getMessage());
		}
	}
}
