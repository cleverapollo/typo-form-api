<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\Form;
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
	 * @param  int $form_id
	 *
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function index($form_id)
	{
		$form = Form::find($form_id);

		// Send error if form does not exist
		if (!$form) {
			return $this->returnError('form', 404, 'show triggers');
		}

		$triggers = $form->triggers()->get();

		return $this->returnSuccessMessage('triggers', QuestionTriggerResource::collection($triggers));
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @param  $form_id
	 * @param  Request $request
	 *
	 * @return \Illuminate\Http\JsonResponse
	 * @throws \Illuminate\Validation\ValidationException
	 */
	public function store($form_id, Request $request)
	{
		$this->validate($request, [
			'question_id' => 'required|integer|min:1',
			'parent_question_id' => 'required|integer|min:1',
			'parent_answer_id' => 'nullable|integer|min:1',
			'comparator_id' => 'required|integer|min:1',
			'order' => 'required|integer|min:1',
			'operator' => 'required|boolean'
		]);

		try {
			$form = Form::find($form_id);

			// Send error if form does not exist
			if (!$form) {
				return $this->returnError('form', 404, 'create trigger');
			}

			$question = Question::find($request->input('question_id'));

			// Send error if question does not exist
			if (!$question) {
				return $this->returnError('question', 404, 'create trigger');
			}

			$parent_question = Question::find($request->input('parent_question_id'));

			// Send error if parent question does not exist
			if (!$parent_question) {
				return $this->returnError('parent question', 404, 'create trigger');
			}

			$parent_answer = Question::find($request->input('parent_answer_id'));

			// Send error if parent answer does not exist
			if (!$parent_answer) {
				return $this->returnError('parent answer', 404, 'create trigger');
			}

			$comparator = Comparator::find($request->input('comparator_id'));

			// Send error if comparator does not exist
			if (!$comparator) {
				return $this->returnError('comparator', 404, 'create trigger');
			}

			// Count order
//			$order = 1;
//			if (count($question->triggers) > 0) {
//				$order = $form->triggers()->max('order') + 1;
//			}

			// Create trigger
			$trigger = $form->triggers()->create([
				'question_id' => $question->id,
				'parent_question_id' => $parent_question->id,
				'parent_answer_id' => $parent_answer->id,
				'value' => $request->input('value', null),
				'comparator_id' => $comparator->id,
				'order' => $request->input('order'),
				'operator' => $request->input('operator')
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
			return $this->returnError('form', 404, 'show trigger');
		}

		$trigger = $form->triggers()->find($id);
		if ($trigger) {
			return $this->returnSuccessMessage('trigger', new QuestionTriggerResource($trigger));
		}

		// Send error if trigger does not exist
		return $this->returnError('trigger', 404, 'show');
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
			'question_id' => 'filled|integer|min:1',
			'parent_question_id' => 'filled|integer|min:1',
			'parent_answer_id' => 'nullable|integer|min:1',
			'comparator_id' => 'filled|integer|min:1',
			'order' => 'filled|integer|min:1',
			'operator' => 'filled|boolean'
		]);

		try {
			$form = Form::find($form_id);

			// Send error if form does not exist
			if (!$form) {
				return $this->returnError('form', 404, 'update trigger');
			}

			$trigger = $form->triggers()->find($id);

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
				$parent_answer = Question::find($parent_answer_id);

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
			if ($trigger->fill($request->only('question_id', 'parent_question_id', 'parent_answer_id', 'value', 'comparator_id', 'order', 'operator'))->save()) {
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
				return $this->returnError('form', 404, 'delete trigger');
			}

			$trigger = $form->triggers()->find($id);

			// Send error if trigger does not exist
			if (!$trigger) {
				return $this->returnError('trigger', 404, 'delete');
			}

			if ($trigger->delete()) {
				return $this->returnSuccessMessage('message', 'QuestionTrigger has been deleted successfully.');
			}

			// Send error if there is an error on update
			return $this->returnError('trigger', 503, 'delete');
		} catch (Exception $e) {
			// Send error
			return $this->returnErrorMessage(503, $e->getMessage());
		}
	}
}
