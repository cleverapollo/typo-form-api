<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\Section;
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
			'question'         => 'required',
			'description'      => 'required',
			'mandatory'        => 'boolean',
			'group_id'         => 'nullable|integer|min:1',
			'question_type_id' => 'required|integer|min:1',
			'order'            => 'required|integer|min:0'
		]);

		try {
			$section = Section::find($section_id);

			// Send error if section does not exist
			if (!$section) {
				return $this->returnError('section', 404, 'create question');
			}

			// Create question
			$question = $section->questions()->create($request->only('question', 'description', 'mandatory', 'group_id', 'question_type_id', 'order'));

			if ($question) {
				return $this->returnSuccessMessage('question', new QuestionResource($question));
			}

			// Send error if question is not created
			return $this->returnError('question', 503, 'create');
		} catch (Exception $e) {
			// Send error
			return $this->returnErrorMessage($e->getCode(), $e->getMessage());
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

		$question = $section->questions()->where('id', $id)->first();
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
			'question'         => 'filled',
			'description'      => 'filled',
			'mandatory'        => 'boolean',
			'group_id'         => 'nullable|integer|min:1',
			'question_type_id' => 'filled|integer|min:1',
			'order'            => 'filled|integer|min:0'
		]);

		try {
			$section = Section::find($section_id);

			// Send error if section does not exist
			if (!$section) {
				return $this->returnError('section', 404, 'update question');
			}

			$question = $section->questions()->where('id', $id)->first();

			// Send error if question does not exist
			if (!$question) {
				return $this->returnError('question', 404, 'update');
			}

			// Update question
			if ($question->fill($request->only('question', 'description', 'mandatory', 'group_id', 'question_type_id', 'order'))->save()) {
				return $this->returnSuccessMessage('question', new QuestionResource($question));
			}

			// Send error if there is an error on update
			return $this->returnError('question', 503, 'update');
		} catch (Exception $e) {
			// Send error
			return $this->returnErrorMessage($e->getCode(), $e->getMessage());
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

			$question = $section->questions()->where('id', $id)->first();

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
			return $this->returnErrorMessage($e->getCode(), $e->getMessage());
		}
	}
}
