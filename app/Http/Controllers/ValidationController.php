<?php

namespace App\Http\Controllers;

use Auth;
use Exception;
use App\Models\Form;
use App\Models\Question;
use App\Models\ValidationType;
use App\Http\Resources\ValidationResource;
use Illuminate\Http\Request;

class ValidationController extends Controller
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
			return $this->returnError('form', 404, 'show validations');
		}

		$validations = $form->validations()->get();

		return $this->returnSuccessMessage('validations', ValidationResource::collection($validations));
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @param  int $form_id
	 * @param  \Illuminate\Http\Request $request
	 *
	 * @return \Illuminate\Http\JsonResponse
	 * @throws \Illuminate\Validation\ValidationException
	 */
	public function store($form_id, Request $request)
	{
		$this->validate($request, [
			'question_id' => 'required|integer|min:1',
			'validation_type_id' => 'required|integer|min:1'
		]);

		try {
			$form = Form::find($form_id);

			// Send error if form does not exist
			if (!$form) {
				return $this->returnError('form', 404, 'create validation');
			}

			$question_id = $request->input('question_id');

			// Send error if question does not exist
			if (!Question::find($question_id)) {
				return $this->returnError('question', 404, 'create validation');
			}

			$validation_type_id = $request->input('validation_type_id');

			// Send error if validation type does not exist
			if (!ValidationType::find($validation_type_id)) {
				return $this->returnError('validation type', 404, 'create validation');
			}

			// Create validation
			$validation = $form->validations()->create([
				'question_id' => $question_id,
				'validation_type_id' => $validation_type_id,
				'validation_data' => $request->input('validation_data', null)
			]);

			if ($validation) {
				return $this->returnSuccessMessage('validation', new ValidationResource($validation));
			}

			// Send error if validation is not created
			return $this->returnError('validation', 503, 'create');
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
			return $this->returnError('form', 404, 'show validation');
		}

		$validation = $form->validations()->find($id);
		if ($validation) {
			return $this->returnSuccessMessage('validation', new ValidationResource($validation));
		}

		// Send error if validation does not exist
		return $this->returnError('validation', 404, 'show');
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
			'validation_type_id' => 'filled|integer|min:1'
		]);

		try {
			$form = Form::find($form_id);

			// Send error if form does not exist
			if (!$form) {
				return $this->returnError('form', 404, 'update validation');
			}

			$validation = $form->validations()->find($id);

			// Send error if validation does not exist
			if (!$validation) {
				return $this->returnError('validation', 404, 'update');
			}

			$validation_type_id = $request->input('validation_type_id', null);

			// Send error if validation type does not exist
			if ($validation_type_id && !ValidationType::find($validation_type_id)) {
				return $this->returnError('validation type', 404, 'update validation');
			}

			// Update validation
			if ($validation->fill($request->only('validation_type_id', 'validation_data'))->save()) {
				return $this->returnSuccessMessage('validation', new ValidationResource($validation));
			}

			// Send error if there is an error on update
			return $this->returnError('validation', 503, 'update');
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
				return $this->returnError('form', 404, 'delete validation');
			}

			$validation = $form->validations()->find($id);

			// Send error if validation does not exist
			if (!$validation) {
				return $this->returnError('validation', 404, 'delete');
			}

			if ($validation->delete()) {
				return $this->returnSuccessMessage('message', 'Validation has been deleted successfully.');
			}

			// Send error if there is an error on delete
			return $this->returnError('validation', 503, 'delete');
		} catch (Exception $e) {
			// Send error
			return $this->returnErrorMessage(503, $e->getMessage());
		}
	}
}
