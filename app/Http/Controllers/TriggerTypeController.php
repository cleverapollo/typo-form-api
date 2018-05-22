<?php

namespace App\Http\Controllers;

use Auth;
use Exception;
use App\Models\QuestionType;
use App\Models\Comparator;
use App\Models\TriggerType;
use App\Http\Resources\TriggerTypeResource;
use Illuminate\Http\Request;

class TriggerTypeController extends Controller
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
		$trigger_types = TriggerType::all();
		return $this->returnSuccessMessage('trigger_types', TriggerTypeResource::collection($trigger_types));
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @param  \Illuminate\Http\Request $request
	 *
	 * @return \Illuminate\Http\JsonResponse
	 * @throws \Illuminate\Validation\ValidationException
	 */
	public function store(Request $request)
	{
		$this->validate($request, [
			'question_type_id' => 'required|integer|min:1',
			'comparator_id' => 'required|integer|min:1',
			'answer' => 'required|boolean',
			'value' => 'required|boolean'
		]);

		try {
			if (!$this->hasPermission()) {
				return $this->returnError('trigger type', 403, 'create');
			}

			// Check whether the question type exists or not
			$question_type_id = $request->input('question_type_id');
			if (!QuestionType::find($question_type_id)) {
				return $this->returnError('question type', 404, 'create trigger type');
			}

			// Check whether the comparator exists or not
			$comparator = Comparator::find($request->input('comparator_id'));
			if (!$comparator) {
				return $this->returnError('comparator', 404, 'create trigger type');
			}

			// Create trigger type
			$trigger_type = TriggerType::create($request->only('question_type_id', 'comparator_id', 'answer', 'value'));

			if ($trigger_type) {
				return $this->returnSuccessMessage('trigger type', new TriggerTypeResource($trigger_type));
			}

			// Send error if trigger type is not created
			return $this->returnError('trigger type', 503, 'create');
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
		$trigger_type = TriggerType::find($id);
		if ($trigger_type) {
			return $this->returnSuccessMessage('trigger type', new TriggerTypeResource($trigger_type));
		}

		// Send error if trigger type does not exist
		return $this->returnError('trigger type', 404, 'show');
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  int $id
	 * @param  \Illuminate\Http\Request $request
	 *
	 * @return \Illuminate\Http\JsonResponse
	 * @throws \Illuminate\Validation\ValidationException
	 */
	public function update($id, Request $request)
	{
		$this->validate($request, [
			'question_type_id' => 'filled|integer|min:1',
			'comparator_id' => 'filled|integer|min:1',
			'answer' => 'filled|boolean',
			'value' => 'filled|boolean'
		]);

		try {
			if (!$this->hasPermission()) {
				return $this->returnError('trigger type', 403, 'update');
			}

			$trigger_type = TriggerType::find($id);

			// Send error if trigger type does not exist
			if (!$trigger_type) {
				return $this->returnError('trigger type', 404, 'update');
			}

			if ($question_type_id = $request->input('question_type_id', null)) {
				// Check whether the question type exists or not
				$question_type = QuestionType::find($question_type_id);
				if (!$question_type) {
					return $this->returnError('question type', 404, 'update trigger type');
				}
			}

			if ($comparator_id = $request->input('comparator_id', null)) {
				// Check whether the comparator exists or not
				$comparator = Comparator::find($comparator_id);
				if (!$comparator) {
					return $this->returnError('comparator', 404, 'update trigger type');
				}
			}

			// Update trigger type
			if ($trigger_type->fill($request->only('question_type_id', 'comparator_id', 'answer', 'value'))->save()) {
				return $this->returnSuccessMessage('trigger type', new TriggerTypeResource($trigger_type));
			}

			// Send error if there is an error on update
			return $this->returnError('trigger type', 503, 'update');
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
			if (!$this->hasPermission()) {
				return $this->returnError('trigger type', 403, 'delete');
			}

			$trigger_type = TriggerType::find($id);

			// Send error if trigger type does not exist
			if (!$trigger_type) {
				return $this->returnError('trigger type', 404, 'delete');
			}

			// Delete trigger type
			if ($trigger_type->delete()) {
				return $this->returnSuccessMessage('message', 'Trigger Type has been deleted successfully.');
			}

			// Send error if there is an error on delete
			return $this->returnError('trigger type', 503, 'delete');
		} catch (Exception $e) {
			// Send error
			return $this->returnErrorMessage(503, $e->getMessage());
		}
	}

	/**
	 * Check whether user is Super Admin or not
	 *
	 * @return bool
	 */
	protected function hasPermission()
	{
		$user = Auth::user();
		if ($user->role->name != 'Super Admin') {
			return false;
		}

		return true;
	}
}
