<?php

namespace App\Http\Controllers;

use App\Models\Question;
use App\Http\Resources\AnswerResource;
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
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function store($question_id, Request $request)
	{
		$question = Question::find($question_id);

		// Send error if question does not exist
		if (!$question) {
			return $this->returnErrorMessage('question', 404, 'create answer');
		}

		// Create answer
		$answer = $question->answers()->create($request->only('answer', 'order'));

		if ($answer) {
			return $this->returnSuccessMessage('answer', new AnswerResource($answer));
		}

		// Send error if answer is not created
		return $this->returnErrorMessage('answer', 503, 'create');
	}

	/**
	 * Display the specified resource.
	 *
	 * @param  int $question_id
	 * @param  int $id
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function show($question_id, $id)
	{
		$question = Question::find($question_id);

		// Send error if question does not exist
		if (!$question) {
			return $this->returnErrorMessage('question', 404, 'show answer');
		}

		$answer = $question->answers()->where('id', $id)->first();
		if ($answer) {
			return $this->returnSuccessMessage('answer', new AnswerResource($answer));
		}

		// Send error if answer does not exist
		return $this->returnErrorMessage('answer', 404, 'show');
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  int $question_id
	 * @param  \Illuminate\Http\Request $request
	 * @param  int $id
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function update($question_id, Request $request, $id)
	{
		$question = Question::find($question_id);

		// Send error if question does not exist
		if (!$question) {
			return $this->returnErrorMessage('question', 404, 'update answer');
		}

		$answer = $question->answers()->where('id', $id)->first();

		// Send error if answer does not exist
		if (!$answer) {
			return $this->returnErrorMessage('answer', 404, 'update');
		}

		// Update answer
		if ($answer->fill($request->only('answer', 'order'))->save()) {
			return $this->returnSuccessMessage('answer', new AnswerResource($answer));
		}

		// Send error if there is an error on update
		return $this->returnErrorMessage('answer', 503, 'update');
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int $question_id
	 * @param  int $id
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function destroy($question_id, $id)
	{
		$question = Question::find($question_id);

		// Send error if question does not exist
		if (!$question) {
			return $this->returnErrorMessage('question', 404, 'delete answer');
		}

		$answer = $question->answers()->where('id', $id)->first();

		// Send error if answer does not exist
		if (!$answer) {
			return $this->returnErrorMessage('answer', 404, 'delete');
		}

		if ($answer->delete()) {
			return $this->returnSuccessMessage('message', 'Answer has been deleted successfully.');
		}

		// Send error if there is an error on update
		return $this->returnErrorMessage('answer', 503, 'delete');
	}
}
