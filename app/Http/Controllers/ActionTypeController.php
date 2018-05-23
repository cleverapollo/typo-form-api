<?php

namespace App\Http\Controllers;

use Auth;
use Exception;
use App\Models\ActionType;
use App\Http\Resources\ActionTypeResource;
use Illuminate\Http\Request;

class ActionTypeController extends Controller
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
		$action_types = ActionType::all();
		return $this->returnSuccessMessage('action_types', ActionTypeResource::collection($action_types));
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
				return $this->returnError('action type', 403, 'create');
			}

			// Create action type
			$action_type = ActionType::create($request->only('type'));

			if ($action_type) {
				return $this->returnSuccessMessage('action type', new ActionTypeResource($action_type));
			}

			// Send error if action type is not created
			return $this->returnError('action type', 503, 'create');
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
		$action_type = ActionType::find($id);
		if ($action_type) {
			return $this->returnSuccessMessage('action type', new ActionTypeResource($action_type));
		}

		// Send error if action type does not exist
		return $this->returnError('action type', 404, 'show');
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
				return $this->returnError('action type', 403, 'update');
			}

			$action_type = ActionType::find($id);

			// Send error if action type does not exist
			if (!$action_type) {
				return $this->returnError('action type', 404, 'update');
			}

			// Update action type
			if ($action_type->fill($request->only('type'))->save()) {
				return $this->returnSuccessMessage('action type', new ActionTypeResource($action_type));
			}

			// Send error if there is an error on update
			return $this->returnError('action type', 503, 'update');
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
				return $this->returnError('action type', 403, 'delete');
			}

			$action_type = ActionType::find($id);

			// Send error if action type does not exist
			if (!$action_type) {
				return $this->returnError('action type', 404, 'delete');
			}

			// Delete action type
			if ($action_type->delete()) {
				return $this->returnSuccessMessage('message', 'Action Type has been deleted successfully.');
			}

			// Send error if there is an error on delete
			return $this->returnError('action type', 503, 'delete');
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
