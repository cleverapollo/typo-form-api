<?php

namespace App\Http\Controllers;

use Auth;
use Exception;
use App\Models\ValidationType;
use App\Http\Resources\ValidationTypeResource;
use Illuminate\Http\Request;

class ValidationTypeController extends Controller
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
		$validation_types = ValidationType::all();
		return $this->returnSuccessMessage('validation_types', ValidationTypeResource::collection($validation_types));
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
			'type' => 'required|max:191'
		]);

		try {
			if (!$this->hasPermission()) {
				return $this->returnError('validation type', 403, 'create');
			}

			// Create validation type
			$validation_type = ValidationType::create($request->only('type'));

			if ($validation_type) {
				return $this->returnSuccessMessage('validation type', new ValidationTypeResource($validation_type));
			}

			// Send error if validation type is not created
			return $this->returnError('validation type', 503, 'create');
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
		$validation_type = ValidationType::find($id);
		if ($validation_type) {
			return $this->returnSuccessMessage('validation type', new ValidationTypeResource($validation_type));
		}

		// Send error if validation type does not exist
		return $this->returnError('validation type', 404, 'show');
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
			'type' => 'filled|max:191'
		]);

		try {
			if (!$this->hasPermission()) {
				return $this->returnError('validation type', 403, 'update');
			}

			$validation_type = ValidationType::find($id);

			// Send error if validation type does not exist
			if (!$validation_type) {
				return $this->returnError('validation type', 404, 'update');
			}

			// Update validation type
			if ($validation_type->fill($request->only('type'))->save()) {
				return $this->returnSuccessMessage('validation type', new ValidationTypeResource($validation_type));
			}

			// Send error if there is an error on update
			return $this->returnError('validation type', 503, 'update');
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
				return $this->returnError('validation type', 403, 'delete');
			}

			$validation_type = ValidationType::find($id);

			// Send error if validation type does not exist
			if (!$validation_type) {
				return $this->returnError('validation type', 404, 'delete');
			}

			// Delete validation type
			if ($validation_type->delete()) {
				return $this->returnSuccessMessage('message', 'Validation Type has been deleted successfully.');
			}

			// Send error if there is an error on update
			return $this->returnError('validation type', 503, 'delete');
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
