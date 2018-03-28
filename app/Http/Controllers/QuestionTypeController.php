<?php

namespace App\Http\Controllers;

use Auth;
use Exception;
use App\Models\QuestionType;
use App\Http\Resources\QuestionTypeResource;
use Illuminate\Http\Request;

class QuestionTypeController extends Controller
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
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function index()
	{
		$question_types = QuestionType::all();
		return $this->returnSuccessMessage('question_types', QuestionTypeResource::collection($question_types));
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @param  \Illuminate\Http\Request $request
	 *
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function store(Request $request)
	{
		$this->validate($request, [
			'type' => 'required|max:191'
		]);

		try {
			// Check whether user is SuperAdmin or not
			$user = Auth::user();
			if ($user->role_id != 1) {
				return $this->returnError('question type', 403, 'create');
			}

			// Create question type
			$question_type = QuestionType::create([
				'type' => $request->input('type')
			]);

			if ($question_type) {
				return $this->returnSuccessMessage('question type', new QuestionTypeResource($question_type));
			}

			// Send error if question type is not created
			return $this->returnError('question type', 503, 'create');
		} catch (Exception $e) {
			// Send error
			return $this->returnErrorMessage(503, $e->getMessage());
		}
	}

	/**
	 * Display the specified resource.
	 *
	 * @param  int $id
	 *
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function show($id)
	{
		$question_type = QuestionType::find($id);
		if ($question_type) {
			return $this->returnSuccessMessage('question type', new QuestionTypeResource($question_type));
		}

		// Send error if question type does not exist
		return $this->returnError('question type', 404, 'show');
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  int $id
	 * @param  \Illuminate\Http\Request $request
	 *
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function update($id, Request $request)
	{
		$this->validate($request, [
			'type' => 'filled|max:191'
		]);

		try {
			// Check whether user is SuperAdmin or not
			$user = Auth::user();
			if ($user->role_id != 1) {
				return $this->returnError('question type', 403, 'update');
			}

			$question_type = QuestionType::find($id);

			// Send error if question type does not exist
			if (!$question_type) {
				return $this->returnError('question type', 404, 'update');
			}

			// Update question type
			if ($question_type->fill($request->only('type'))->save()) {
				return $this->returnSuccessMessage('question type', new QuestionTypeResource($question_type));
			}

			// Send error if there is an error on update
			return $this->returnError('question type', 503, 'update');
		} catch (Exception $e) {
			// Send error
			return $this->returnErrorMessage(503, $e->getMessage());
		}
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int $id
	 *
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function destroy($id)
	{
		try {
			// Check whether user is SuperAdmin or not
			$user = Auth::user();
			if ($user->role_id != 1) {
				return $this->returnError('question type', 403, 'delete');
			}

			$question_type = QuestionType::find($id);

			// Send error if question type does not exist
			if (!$question_type) {
				return $this->returnError('question type', 404, 'delete');
			}

			// Delete question type
			if ($question_type->delete()) {
				return $this->returnSuccessMessage('message', 'Question Type has been deleted successfully.');
			}

			// Send error if there is an error on update
			return $this->returnError('question type', 503, 'delete');
		} catch (Exception $e) {
			// Send error
			return $this->returnErrorMessage(503, $e->getMessage());
		}
	}
}
