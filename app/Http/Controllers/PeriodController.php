<?php

namespace App\Http\Controllers;

use Auth;
use Exception;
use App\Models\Period;
use App\Http\Resources\PeriodResource;
use Illuminate\Http\Request;

class PeriodController extends Controller
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
		$periods = Period::all();
		return $this->returnSuccessMessage('periods', PeriodResource::collection($periods));
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
			'period' => 'required|max:191'
		]);

		try {
			if (!$this->hasPermission()) {
				return $this->returnError('period', 403, 'create');
			}

			// Create period
			$period = Period::create($request->only('period'));

			if ($period) {
				return $this->returnSuccessMessage('period', new PeriodResource($period));
			}

			// Send error if period is not created
			return $this->returnError('period', 503, 'create');
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
		$period = Period::find($id);
		if ($period) {
			return $this->returnSuccessMessage('period', new PeriodResource($period));
		}

		// Send error if period does not exist
		return $this->returnError('period', 404, 'show');
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
			'period' => 'filled|max:191'
		]);

		try {
			if (!$this->hasPermission()) {
				return $this->returnError('period', 403, 'update');
			}

			$period = Period::find($id);

			// Send error if period does not exist
			if (!$period) {
				return $this->returnError('period', 404, 'update');
			}

			// Update period
			if ($period->fill($request->only('period'))->save()) {
				return $this->returnSuccessMessage('period', new PeriodResource($period));
			}

			// Send error if there is an error on update
			return $this->returnError('period', 503, 'update');
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
				return $this->returnError('period', 403, 'delete');
			}

			$period = Period::find($id);

			// Send error if period does not exist
			if (!$period) {
				return $this->returnError('period', 404, 'delete');
			}

			// Delete period
			if ($period->delete()) {
				return $this->returnSuccessMessage('message', 'Period has been deleted successfully.');
			}

			// Send error if there is an error on delete
			return $this->returnError('period', 503, 'delete');
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
