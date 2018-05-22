<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\Submission;
use App\Models\Question;
use App\Models\Answer;
use App\Http\Resources\ResponseResource;
use Illuminate\Http\Request;

class ResponseController extends Controller
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
	 * @param  int $submission_id
	 *
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function index($submission_id)
	{
		$responses = Submission::find($submission_id)->responses()->get();

		return $this->returnSuccessMessage('responses', ResponseResource::collection($responses));
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @param  int $submission_id
	 * @param  \Illuminate\Http\Request $request
	 *
	 * @return \Illuminate\Http\JsonResponse
	 * @throws \Illuminate\Validation\ValidationException
	 */
	public function store($submission_id, Request $request)
	{
		$this->validate($request, [
			'question_id' => 'required|integer|min:1',
			'answer_id' => 'nullable|integer|min:1',
			'order' => 'nullable|integer|min:1'
		]);

		try {
			$submission = Submission::find($submission_id);

			// Send error if submission does not exist
			if (!$submission) {
				return $this->returnError('submission', 404, 'create response');
			}

			$question_id = $request->input('question_id');

			// Send error if question does not exist
			if (!Question::find($question_id)) {
				return $this->returnError('question', 404, 'create response');
			}

			$answer_id = $request->input('answer_id', null);
			if ($answer_id) {
				// Send error if answer does not exist
				if (!Answer::find($answer_id)) {
					return $this->returnError('answer', 404, 'create response');
				}
			}

			// Create response
			$response = $submission->responses()->create([
				'question_id' => $question_id,
				'response' => $request->input('response', null),
				'answer_id' => $answer_id,
				'order' => $request->input('order', null)
			]);

			if ($response) {
				return $this->returnSuccessMessage('response', new ResponseResource($response));
			}

			// Send error if response is not created
			return $this->returnError('response', 503, 'create');
		} catch (Exception $e) {
			// Send error
			return $this->returnErrorMessage(503, $e->getMessage());
		}
	}

	/**
	 * Display the specified resource.
	 *
	 * @param  int $submission_id
	 * @param  int $id
	 *
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function show($submission_id, $id)
	{
		$submission = Submission::find($submission_id);

		// Send error if submission does not exist
		if (!$submission) {
			return $this->returnError('submission', 404, 'show response');
		}

		$response = $submission->responses()->find($id);
		if ($response) {
			return $this->returnSuccessMessage('response', new ResponseResource($response));
		}

		// Send error if response does not exist
		return $this->returnError('response', 404, 'show');
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  int $submission_id
	 * @param  int $id
	 * @param  \Illuminate\Http\Request $request
	 *
	 * @return \Illuminate\Http\JsonResponse
	 * @throws \Illuminate\Validation\ValidationException
	 */
	public function update($submission_id, $id, Request $request)
	{
		$this->validate($request, [
			'question_id' => 'filled|integer|min:1',
			'answer_id' => 'nullable|integer|min:1',
			'order' => 'nullable|integer|min:1'
		]);

		try {
			$submission = Submission::find($submission_id);

			// Send error if submission does not exist
			if (!$submission) {
				return $this->returnError('submission', 404, 'update response');
			}

			$response = $submission->responses()->find($id);

			// Send error if response does not exist
			if (!$response) {
				return $this->returnError('response', 404, 'update');
			}

			if ($question_id = $request->input('question_id')) {
				// Send error if question does not exist
				if (!Question::find($question_id)) {
					return $this->returnError('question', 404, 'update response');
				}
			}

			if ($answer_id = $request->input('answer_id')) {
				// Send error if answer does not exist
				if (!Answer::find($answer_id)) {
					return $this->returnError('answer', 404, 'update response');
				}
			}

			$newResponse = $response->fill($request->only('question_id', 'response', 'answer_id', 'order'));

			if ($submission->responses()->where('id', $id)->delete()) {
				$new = $submission->responses()->create([
					'question_id' => $newResponse->question_id,
					'response' => $newResponse->response,
					'answer_id' => $newResponse->answer_id,
					'order' => $newResponse->order
				]);

				if ($new) {
					return $this->returnSuccessMessage('response', new ResponseResource($new));
				}
			}

			// Send error if there is an error on update
			return $this->returnError('response', 503, 'update');
		} catch (Exception $e) {
			// Send error
			return $this->returnErrorMessage(503, $e->getMessage());
		}
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int $submission_id
	 * @param  int $id
	 *
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function destroy($submission_id, $id)
	{
		try {
			$submission = Submission::find($submission_id);

			// Send error if submission does not exist
			if (!$submission) {
				return $this->returnError('submission', 404, 'show response');
			}

			$response = $submission->responses()->find($id);

			// Send error if response does not exist
			if (!$response) {
				return $this->returnError('response', 404, 'delete');
			}

			if ($response->delete()) {
				return $this->returnSuccessMessage('message', 'Response has been deleted successfully.');
			}

			// Send error if there is an error on delete
			return $this->returnError('response', 503, 'delete');
		} catch (Exception $e) {
			// Send error
			return $this->returnErrorMessage(503, $e->getMessage());
		}
	}
}
