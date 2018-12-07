<?php

namespace App\Http\Controllers;

use Auth;
use Exception;
use App\Models\Type;
use App\Http\Resources\TypeResource;
use Illuminate\Http\Request;

class TypeController extends Controller
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
		$types = Type::all();
		return $this->returnSuccessMessage('types', TypeResource::collection($types));
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
			'name' => 'required|max:191'
		]);

		try {
			if (!$this->hasPermission()) {
				return $this->returnError('type', 403, 'create');
			}

			// Create type
			$type = Type::create($request->only('name'));

			if ($type) {
				return $this->returnSuccessMessage('type', new TypeResource($type));
			}

			// Send error if type is not created
			return $this->returnError('type', 503, 'create');
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
		$type = Type::find($id);
		if ($type) {
			return $this->returnSuccessMessage('type', new TypeResource($type));
		}

		// Send error if type does not exist
		return $this->returnError('type', 404, 'show');
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
			'name' => 'filled|max:191'
		]);

		try {
			if (!$this->hasPermission()) {
				return $this->returnError('type', 403, 'update');
			}

			$type = Type::find($id);

			// Send error if type does not exist
			if (!$type) {
				return $this->returnError('type', 404, 'update');
			}

			if ($type->name == 'Super Admin' || $type->name == 'Admin' || $type->name == 'User') {
				return $this->returnError('type', 403, 'update');
			}

			// Update type
			if ($type->fill($request->only('name'))->save()) {
				return $this->returnSuccessMessage('type', new TypeResource($type));
			}

			// Send error if there is an error on update
			return $this->returnError('type', 503, 'update');
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
				return $this->returnError('type', 403, 'delete');
			}

			$type = Type::find($id);

			// Send error if type does not exist
			if (!$type) {
				return $this->returnError('type', 404, 'delete');
			}

			if ($type->name == 'Super Admin' || $type->name == 'Admin' || $type->name == 'User') {
				return $this->returnError('type', 403, 'delete');
			}

			// Delete type
			if ($type->delete()) {
				return $this->returnSuccessMessage('message', 'Type has been deleted successfully.');
			}

			// Send error if there is an error on delete
			return $this->returnError('type', 503, 'delete');
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
		if ($user->type->name != 'Super Admin') {
			return false;
		}

		return true;
	}
}
