<?php

namespace App\Http\Controllers;

use Auth;
use Exception;
use App\Models\Role;
use App\Http\Resources\RoleResource;
use Illuminate\Http\Request;

class RoleController extends Controller
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
		$roles = Role::all();
		return $this->returnSuccessMessage('roles', RoleResource::collection($roles));
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
				return $this->returnError('role', 403, 'create');
			}

			// Create role
			$role = Role::create($request->only('name'));

			if ($role) {
				return $this->returnSuccessMessage('role', new RoleResource($role));
			}

			// Send error if role is not created
			return $this->returnError('role', 503, 'create');
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
		$role = Role::find($id);
		if ($role) {
			return $this->returnSuccessMessage('role', new RoleResource($role));
		}

		// Send error if role does not exist
		return $this->returnError('role', 404, 'show');
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
				return $this->returnError('role', 403, 'update');
			}

			$role = Role::find($id);

			// Send error if role does not exist
			if (!$role) {
				return $this->returnError('role', 404, 'update');
			}

			if ($role->name == 'Super Admin' || $role->name == 'Admin' || $role->name == 'User') {
				return $this->returnError('role', 403, 'update');
			}

			// Update role
			if ($role->fill($request->only('name'))->save()) {
				return $this->returnSuccessMessage('role', new RoleResource($role));
			}

			// Send error if there is an error on update
			return $this->returnError('role', 503, 'update');
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
				return $this->returnError('role', 403, 'delete');
			}

			$role = Role::find($id);

			// Send error if role does not exist
			if (!$role) {
				return $this->returnError('role', 404, 'delete');
			}

			if ($role->name == 'Super Admin' || $role->name == 'Admin' || $role->name == 'User') {
				return $this->returnError('role', 403, 'delete');
			}

			// Delete role
			if ($role->delete()) {
				return $this->returnSuccessMessage('message', 'Role has been deleted successfully.');
			}

			// Send error if there is an error on update
			return $this->returnError('role', 503, 'delete');
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
