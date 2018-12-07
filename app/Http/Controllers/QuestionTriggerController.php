<?php

namespace App\Http\Controllers;

use App\Models\Answer;
use Exception;
use App\Models\FormTemplate;
use App\Models\Section;
use App\Models\Question;
use App\Models\Comparator;
use App\Http\Resources\QuestionTriggerResource;
use Illuminate\Http\Request;

class QuestionTriggerController extends Controller
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
		$form_template = FormTemplate::find($form_template_id);

		// Send error if form template does not exist
		if (!$form_template) {
			return $this->returnError('form_template', 404, 'show triggers');
		}

		$triggers = $form_template->triggers()->get();

		return $this->returnSuccessMessage('triggers', QuestionTriggerResource::collection($triggers));
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @param  $form_template_id
	 * @param  Request $request
	 *
	 * @return \Illuminate\Http\JsonResponse
	 * @throws \Illuminate\Validation\ValidationException
	 */
	public function store($form_template_id, Request $request)
	{
		$this->validate($request, [
			'question_id' => 'required|integer|min:1',
			'parent_question_id' => 'required|integer|min:1',
			'parent_answer_id' => 'nullable|integer|min:1',
			'comparator_id' => 'required|integer|min:1',
//			'order' => 'required|integer|min:1',
			'operator' => 'required|boolean',
			'type' => 'required|string'
		]);

		try {
			$form_template = FormTemplate::find($form_template_id);

			// Send error if form template does not exist
			if (!$form_template) {
				return $this->returnError('form_template', 404, 'create trigger');
			}

			// Check type of trigger
			$type = $request->input('type');

			if($type == 'Section') {
				$element = Section::find($request->input('question_id'));
			} else {
				$element = Question::find($request->input('question_id'));
			}

			// Send error if question does not exist
			if (!$element) {
				return $this->returnError('question', 404, 'create trigger');
			}

			$parent_question = Question::find($request->input('parent_question_id'));

			// Send error if parent question does not exist
			if (!$parent_question) {
				return $this->returnError('parent question', 404, 'create trigger');
			}

			if ($parent_answer_id = $request->input('parent_answer_id', null)) {
				$parent_answer = Answer::find($parent_answer_id);

				// Send error if parent answer does not exist
				if (!$parent_answer) {
					return $this->returnError('parent answer', 404, 'create trigger');
				}
			}

			$comparator = Comparator::find($request->input('comparator_id'));

			// Send error if comparator does not exist
			if (!$comparator) {
				return $this->returnError('comparator', 404, 'create trigger');
			}

			// Count order
			$order = 1;
			if (count($element->triggers) > 0) {
				$order = $form_template->triggers()->where('question_id', $element->id)->max('order') + 1;
			}

			// Create trigger
			$trigger = $form_template->triggers()->create([
				'question_id' => $element->id,
				'parent_question_id' => $parent_question->id,
				'parent_answer_id' => $parent_answer_id,
				'value' => $request->input('value', null),
				'comparator_id' => $comparator->id,
				'order' => $order,
				'operator' => $request->input('operator'),
				'type' => $request->input('type')
			]);

			if ($trigger) {
				return $this->returnSuccessMessage('trigger', new QuestionTriggerResource($trigger));
			}

			// Send error if trigger is not created
			return $this->returnError('trigger', 503, 'create');
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
			return $this->returnError('form_template', 404, 'show trigger');
		}

		$trigger = $form_template->triggers()->find($id);
		if ($trigger) {
			return $this->returnSuccessMessage('trigger', new QuestionTriggerResource($trigger));
		}

		// Send error if trigger does not exist
		return $this->returnError('trigger', 404, 'show');
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
			'question_id' => 'filled|integer|min:1',
			'parent_question_id' => 'filled|integer|min:1',
			'parent_answer_id' => 'nullable|integer|min:1',
			'comparator_id' => 'filled|integer|min:1',
//			'order' => 'filled|integer|min:1',
			'operator' => 'filled|boolean',
			'type' => 'filled|string'
		]);

		try {
			$form_template = FormTemplate::find($form_template_id);

			// Send error if form template does not exist
			if (!$form_template) {
				return $this->returnError('form template', 404, 'update trigger');
			}

			$trigger = $form_template->triggers()->find($id);

			// Send error if trigger does not exist
			if (!$trigger) {
				return $this->returnError('trigger', 404, 'update');
			}

			if ($question_id = $request->input('question_id', null)) {
				$question = Question::find($question_id);

				// Send error if question does not exist
				if (!$question) {
					return $this->returnError('question', 404, 'update trigger');
				}
			}

			if ($parent_question_id = $request->input('parent_question_id', null)) {
				$parent_question = Question::find($parent_question_id);

				// Send error if parent question does not exist
				if (!$parent_question) {
					return $this->returnError('parent question', 404, 'update trigger');
				}
			}

			if ($parent_answer_id = $request->input('parent_answer_id', null)) {
				$parent_answer = Answer::find($parent_answer_id);

				// Send error if parent answer does not exist
				if (!$parent_answer) {
					return $this->returnError('parent answer', 404, 'update trigger');
				}
			}

			if ($comparator_id = $request->input('comparator_id', null)) {
				$comparator = Comparator::find($comparator_id);

				// Send error if comparator does not exist
				if (!$comparator) {
					return $this->returnError('comparator', 404, 'update trigger');
				}
			}

			// Update trigger
			if ($trigger->fill($request->only('question_id', 'parent_question_id', 'parent_answer_id', 'value', 'comparator_id', 'operator', 'type'))->save()) {
				return $this->returnSuccessMessage('trigger', new QuestionTriggerResource($trigger));
			}

			// Send error if there is an error on update
			return $this->returnError('trigger', 503, 'update');
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

			// Send error if form template does not exist
			if (!$form_template) {
				return $this->returnError('form template', 404, 'delete trigger');
			}

			$trigger = $form_template->triggers()->find($id);

			// Send error if trigger does not exist
			if (!$trigger) {
				return $this->returnError('trigger', 404, 'delete');
			}

			if ($trigger->delete()) {
				return $this->returnSuccessMessage('message', 'QuestionTrigger has been deleted successfully.');
			}

			// Send error if there is an error on delete
			return $this->returnError('trigger', 503, 'delete');
		} catch (Exception $e) {
			// Send error
			return $this->returnErrorMessage(503, $e->getMessage());
		}
	}
}
