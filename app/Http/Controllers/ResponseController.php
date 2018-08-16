<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\Submission;
use App\Models\Form;
use App\Models\Section;
use App\Models\Question;
use App\Models\QuestionType;
use App\Models\Answer;
use App\Models\Response;
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

			$form = Form::find($submission->form_id);

			// Send error if form does not exist
			if (!$form) {
				return $this->returnError('form', 404, 'create response');
			}

			// Get Question and Question Type
			$question = Question::find($question_id);
			$question_type = QuestionType::find($question->question_type_id);
			$responses = $submission->responses->where('question_id',  $question_id);
			$validations = $form->validations->where('question_id', $question_id);
			$response_value = $request->input('response', null);

			if ($question_type->type == 'Short answer' ||
				$question_type->type == 'Paragraph' ||
				$question_type->type == 'Multiple choice' ||
				$question_type->type == 'Linear scale' ||
				$question_type->type == 'Date' ||
				$question_type->type == 'Time' ||
				$question_type->type == 'ABN Lookup' ||
				($question_type->type == 'Dropdown' && !count($validations))) {
				if (count($responses)) {
					return $this->returnErrorMessage(404, 'Response is not allowed to create multiply.');
				}
			} else if ($question_type->type == 'Checkboxes' ||
				$question_type->type == 'Multiple choice grid' ||
				($question_type->type == 'Dropdown' && count($validations))) {
				if (count($responses->where('answer_id', $answer_id))) {
					return $this->returnErrorMessage(404, 'Response is not allowed to create multiply.');
				}
			} else if ($question_type->type == 'Checkbox grid') {
				if (count($responses->where('answer_id', $answer_id)
					->where('response', (string)$response_value))) {
					return $this->returnErrorMessage(404, 'Response is not allowed to create multiply.');
				}
			}

			// Create response
			if ($question_type->type == 'ABN Lookup') {
				$query = urlencode(str_replace(' ', '', $request->input('response', null)));
				$handle = curl_init();
				curl_setopt_array($handle, [
					CURLOPT_RETURNTRANSFER => 1,
					CURLOPT_URL => 'https://abr.business.gov.au/json/AbnDetails.aspx?abn='. $query .'&callback=callback&guid=9c1fe65f-650b-4ea8-838c-aa03d946db12',
					CURLOPT_HTTPHEADER => [
						'Content-Type: application/x-www-form-urlencoded'
					]
				]);
				$data = curl_exec($handle);

				if (curl_error($handle)) {
					return response()->json(curl_error($handle), 500);
				}

				$response_value = substr($data, 9, -1);
			}

			$response = $submission->responses()->create([
				'question_id' => $question_id,
				'response' => $response_value,
				'answer_id' => $answer_id,
				'order' => $request->input('order', null)
			]);

			if ($response) {
				return $this->returnSuccessMessage('response', new ResponseResource(Response::find($response->id)));
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
				$question = Question::find($question_id);
				$question_type = QuestionType::find($question->question_type_id);

				// Create response
				if ($question_type->type == 'ABN Lookup') {
					$query = urlencode(str_replace(' ', '', $newResponse->response));
					$handle = curl_init();
					curl_setopt_array($handle, [
						CURLOPT_RETURNTRANSFER => 1,
						CURLOPT_URL => 'https://abr.business.gov.au/json/AbnDetails.aspx?abn='. $query .'&callback=callback&guid=9c1fe65f-650b-4ea8-838c-aa03d946db12',
						CURLOPT_HTTPHEADER => [
							'Content-Type: application/x-www-form-urlencoded'
						]
					]);
					$data = curl_exec($handle);

					if (curl_error($handle)) {
						return response()->json(curl_error($handle), 500);
					}

					$response_value = substr($data, 9, -1);
				} else {
					$response_value = $newResponse->response;
				}
				$new = $submission->responses()->create([
					'question_id' => $newResponse->question_id,
					'response' => $response_value,
					'answer_id' => $newResponse->answer_id,
					'order' => $newResponse->order
				]);

				if ($new) {
					return $this->returnSuccessMessage('response', new ResponseResource(Response::find($new->id)));
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

	/**
	 * Remove the section resource with order from storage.
	 *
	 * @param  int $submission_id
	 * @param  int $section_id
	 * @param  int $order
	 *
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function deleteSectionResponse($submission_id, $section_id, $order)
	{
		try {
			$submission = Submission::find($submission_id);

			// Send error if submission does not exist
			if (!$submission) {
				return $this->returnError('submission', 404, 'show response');
			}

			$section = Section::find($section_id);

			// Send error if section does not exist
			if (!$section) {
				return $this->returnError('section', 404, 'delete question');
			}

			$section->questions()->get()->each(function ($question) use ($order) {
				$question->responses()->get()->each(function ($response) use ($order) {
					if ($response->order == $order) {
						$response->delete();
					} else if ($response->order > $order) {
						$response->order -= 1;
						$response->save();
					}
				});
			});

			$responses = Submission::find($submission_id)->responses()->get();

			return $this->returnSuccessMessage('responses', ResponseResource::collection($responses));
		} catch (Exception $e) {
			// Send error
			return $this->returnErrorMessage(503, $e->getMessage());
		}
	}
}
