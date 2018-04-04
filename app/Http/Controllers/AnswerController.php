<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\Question;
use App\Http\Resources\AnswerResource;
use App\Http\Resources\QuestionResource;
use Illuminate\Http\Request;

class AnswerController extends Controller
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
	 * @param  int $question_id
	 *
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function index($question_id)
	{
		$answers = Question::find($question_id)->answers()->get();

		return $this->returnSuccessMessage('answers', AnswerResource::collection($answers));
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @param  int $question_id
	 * @param  \Illuminate\Http\Request $request
	 *
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function store($question_id, Request $request)
	{
		$this->validate($request, [
			'parameter' => 'required|boolean'
		]);

		try {
			$question = Question::find($question_id);

			// Send error if question does not exist
			if (!$question) {
				return $this->returnError('question', 404, 'create answer');
			}

			// Count order
			$order = 1;
			if (count($question->answers) > 0) {
				$order = $question->answers()->max('order') + 1;
			}

			// Create answer
			$answer = $question->answers()->create([
				'answer' => $request->input('answer', null),
				'parameter' => $request->input('parameter', 1),
				'order' => $order
			]);

			if ($answer) {
				return $this->returnSuccessMessage('answer', new AnswerResource($answer));
			}

			// Send error if answer is not created
			return $this->returnError('answer', 503, 'create');
		} catch (Exception $e) {
			// Send error
			return $this->returnErrorMessage(503, $e->getMessage());
		}
	}

	/**
	 * Display the specified resource.
	 *
	 * @param  int $question_id
	 * @param  int $id
	 *
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function show($question_id, $id)
	{
		$question = Question::find($question_id);

		// Send error if question does not exist
		if (!$question) {
			return $this->returnError('question', 404, 'show answer');
		}

		$answer = $question->answers()->find($id);
		if ($answer) {
			return $this->returnSuccessMessage('answer', new AnswerResource($answer));
		}

		// Send error if answer does not exist
		return $this->returnError('answer', 404, 'show');
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  int $question_id
	 * @param  \Illuminate\Http\Request $request
	 * @param  int $id
	 *
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function update($question_id, Request $request, $id)
	{
		$this->validate($request, [
			'parameter' => 'filled|boolean'
		]);

		try {
			$question = Question::find($question_id);

			// Send error if question does not exist
			if (!$question) {
				return $this->returnError('question', 404, 'update answer');
			}

			$answer = $question->answers()->find($id);

			// Send error if answer does not exist
			if (!$answer) {
				return $this->returnError('answer', 404, 'update');
			}

			// Update answer
			if ($answer->fill($request->only('answer', 'parameter'))->save()) {
				return $this->returnSuccessMessage('answer', new AnswerResource($answer));
			}

			// Send error if there is an error on update
			return $this->returnError('answer', 503, 'update');
		} catch (Exception $e) {
			// Send error
			return $this->returnErrorMessage(503, $e->getMessage());
		}
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int $question_id
	 * @param  int $id
	 *
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function destroy($question_id, $id)
	{
		try {
			$question = Question::find($question_id);

			// Send error if question does not exist
			if (!$question) {
				return $this->returnError('question', 404, 'delete answer');
			}

			$answer = $question->answers()->find($id);

			// Send error if answer does not exist
			if (!$answer) {
				return $this->returnError('answer', 404, 'delete');
			}

			if ($answer->delete()) {
				return $this->returnSuccessMessage('message', 'Answer has been deleted successfully.');
			}

			// Send error if there is an error on update
			return $this->returnError('answer', 503, 'delete');
		} catch (Exception $e) {
			// Send error
			return $this->returnErrorMessage(503, $e->getMessage());
		}
	}

	/**
	 * Remove the specified resources from storage.
	 *
	 * @param  int $question_id
	 *
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function destroyAll($question_id)
	{
		try {
			$question = Question::find($question_id);

			// Send error if question does not exist
			if (!$question) {
				return $this->returnError('question', 404, 'delete answers');
			}

			$question->answers->each(function ($answer) {
				$answer->delete();
			});

			return $this->returnSuccessMessage('message', 'Answers has been deleted successfully.');
		} catch (Exception $e) {
			// Send error
			return $this->returnErrorMessage(503, $e->getMessage());
			// Send error if there is an error on update
			// return $this->returnError('answer', 503, 'delete');
		}
	}

	/**
	 * Move the specified resources from storage.
	 *
	 * @param  int $question_id
	 * @param  int $id
	 * @param  Request $request
	 *
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function move($question_id, $id, Request $request)
	{
		$this->validate($request, [
			'order' => 'required|integer|min:1'
		]);

		try {
			$question = Question::find($question_id);

			// Send error if question does not exist
			if (!$question) {
				return $this->returnError('question', 404, 'move answer');
			}

			$answer = $question->answers()->find($id);

			// Send error if answer does not exist
			if (!$answer) {
				return $this->returnError('answer', 404, 'move');
			}

			$answer->order = $request->input('order') + 1;
			$answer->save();

			// Update other answers order
			$question->answers()->where([
				['id', '<>', $answer->id],
				['order', '>=', $answer->order]
			])->get()->each(function ($other) {
				$other->order += 1;
				$other->save();
			});

			return $this->returnSuccessMessage('data', new QuestionResource($answer->question));
		} catch (Exception $e) {
			// Send error
			return $this->returnErrorMessage(503, $e->getMessage());
		}
	}
}
