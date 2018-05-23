<?php

namespace App\Http\Controllers;

use Auth;
use Exception;
use App\Models\Status;
use App\Http\Resources\StatusResource;
use Illuminate\Http\Request;

class StatusController extends Controller
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
		$statuses = Status::all();
		return $this->returnSuccessMessage('statuses', StatusResource::collection($statuses));
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
			'status' => 'required|max:191'
		]);

		try {
			if (!$this->hasPermission()) {
				return $this->returnError('status', 403, 'create');
			}

			// Create status
			$status = Status::create($request->only('status'));

			if ($status) {
				return $this->returnSuccessMessage('status', new StatusResource($status));
			}

			// Send error if status is not created
			return $this->returnError('status', 503, 'create');
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
		$status = Status::find($id);
		if ($status) {
			return $this->returnSuccessMessage('status', new StatusResource($status));
		}

		// Send error if status does not exist
		return $this->returnError('status', 404, 'show');
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
			'status' => 'filled|max:191'
		]);

		try {
			if (!$this->hasPermission()) {
				return $this->returnError('status', 403, 'update');
			}

			$status = Status::find($id);

			// Send error if status does not exist
			if (!$status) {
				return $this->returnError('status', 404, 'update');
			}

			// Update status
			if ($status->fill($request->only('status'))->save()) {
				return $this->returnSuccessMessage('status', new StatusResource($status));
			}

			// Send error if there is an error on update
			return $this->returnError('status', 503, 'update');
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
				return $this->returnError('status', 403, 'delete');
			}

			$status = Status::find($id);

			// Send error if status does not exist
			if (!$status) {
				return $this->returnError('status', 404, 'delete');
			}

			// Delete status
			if ($status->delete()) {
				return $this->returnSuccessMessage('message', 'Status has been deleted successfully.');
			}

			// Send error if there is an error on delete
			return $this->returnError('status', 503, 'delete');
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
