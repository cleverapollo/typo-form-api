<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\Section;
use App\Models\Question;
use App\Http\Resources\QuestionResource;
use Illuminate\Http\Request;

class QuestionController extends Controller
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
	 * @param  int $section_id
	 *
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function index($section_id)
	{
		$questions = Section::find($section_id)->questions()->get();

		return $this->returnSuccessMessage('questions', QuestionResource::collection($questions));
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @param  int $section_id
	 * @param  \Illuminate\Http\Request $request
	 *
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function store($section_id, Request $request)
	{
		$this->validate($request, [
			'question' => 'required',
			'description' => 'required',
			'mandatory' => 'required|boolean',
			'question_type_id' => 'required|integer|min:1',
			'order' => 'filled|integer|min:0'
		]);

		try {
			$section = Section::find($section_id);

			// Send error if section does not exist
			if (!$section) {
				return $this->returnError('section', 404, 'create question');
			}

			$order = $request->input('order');
			if (!$order) {
				if (Question::where('section_id', $section_id)->exists()) {
					$order = Question::where('section_id', $section_id)->max('order') + 1;
				} else {
					$order = 0;
				}
			}

			// Create question
			$question = $section->questions()->create([
				'question' => $request->input('question'),
				'description' => $request->input('description'),
				'mandatory' => $request->input('mandatory'),
				'question_type_id' => $request->input('question_type_id'),
				'order' => $order
			]);

			if ($question) {
				return $this->returnSuccessMessage('question', new QuestionResource($question));
			}

			// Send error if question is not created
			return $this->returnError('question', 503, 'create');
		} catch (Exception $e) {
			// Send error
			return $this->returnErrorMessage(503, $e->getMessage());
		}
	}

	/**
	 * Duplicate a resource in storage.
	 *
	 * @param  int $section_id
	 * @param  int $id
	 *
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function duplicate($section_id, $id)
	{
		try {
			$section = Section::find($section_id);

			// Send error if section does not exist
			if (!$section) {
				return $this->returnError('section', 404, 'create question');
			}

			$question = $section->questions()->find($id);

			// Send error if question does not exist
			if (!$question) {
				return $this->returnError('question', 404, 'duplicate');
			}

			// Duplicate question
			$newQuestion = $section->questions()->create([
				'question' => $question->question,
				'description' => $question->description,
				'mandatory' => $question->mandatory,
				'question_type_id' => $question->question_type_id,
				'order' => ($question->order + 1)
			]);

			if ($newQuestion) {
				// Update other questions order
				$section->questions()->where('order', '>=', $newQuestion->order)->get()->each(function ($other) {
					$other->order += 1;
					$other->save();
				});

				return $this->returnSuccessMessage('question', new QuestionResource($newQuestion));
			}

			// Send error if question is not created
			return $this->returnError('question', 503, 'duplicate');
		} catch (Exception $e) {
			// Send error
			return $this->returnErrorMessage(503, $e->getMessage());
		}
	}

	/**
	 * Display the specified resource.
	 *
	 * @param  int $section_id
	 * @param  int $id
	 *
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function show($section_id, $id)
	{
		$section = Section::find($section_id);

		// Send error if section does not exist
		if (!$section) {
			return $this->returnError('section', 404, 'show question');
		}

		$question = $section->questions()->find($id);
		if ($question) {
			return $this->returnSuccessMessage('question', new QuestionResource($question));
		}

		// Send error if question does not exist
		return $this->returnError('question', 404, 'show');
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  int $section_id
	 * @param  \Illuminate\Http\Request $request
	 * @param  int $id
	 *
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function update($section_id, Request $request, $id)
	{
		$this->validate($request, [
			'question' => 'filled',
			'description' => 'filled',
			'mandatory' => 'filled|boolean',
			'question_type_id' => 'filled|integer|min:1',
			'order' => 'filled|integer|min:0'
		]);

		try {
			$section = Section::find($section_id);

			// Send error if section does not exist
			if (!$section) {
				return $this->returnError('section', 404, 'update question');
			}

			$question = $section->questions()->find($id);

			// Send error if question does not exist
			if (!$question) {
				return $this->returnError('question', 404, 'update');
			}

			// Update question
			if ($question->fill($request->only('question', 'description', 'mandatory', 'question_type_id', 'order'))->save()) {
				return $this->returnSuccessMessage('question', new QuestionResource($question));
			}

			// Send error if there is an error on update
			return $this->returnError('question', 503, 'update');
		} catch (Exception $e) {
			// Send error
			return $this->returnErrorMessage(503, $e->getMessage());
		}
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int $section_id
	 * @param  int $id
	 *
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function destroy($section_id, $id)
	{
		try {
			$section = Section::find($section_id);

			// Send error if section does not exist
			if (!$section) {
				return $this->returnError('section', 404, 'delete question');
			}

			$question = $section->questions()->find($id);

			// Send error if question does not exist
			if (!$question) {
				return $this->returnError('question', 404, 'delete');
			}

			if ($question->delete()) {
				return $this->returnSuccessMessage('message', 'Question has been deleted successfully.');
			}

			// Send error if there is an error on update
			return $this->returnError('question', 503, 'delete');
		} catch (Exception $e) {
			// Send error
			return $this->returnErrorMessage(503, $e->getMessage());
		}
	}
}
