<?php

namespace App\Http\Controllers;

use Auth;
use Exception;
use App\Models\Comparator;
use App\Http\Resources\ComparatorResource;
use Illuminate\Http\Request;

class ComparatorController extends Controller
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
		$comparators = Comparator::all();
		return $this->returnSuccessMessage('comparators', ComparatorResource::collection($comparators));
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
			'comparator' => 'required|max:191'
		]);

		try {
			if (!$this->hasPermission()) {
				return $this->returnError('comparator', 403, 'create');
			}

			// Create comparator
			$comparator = Comparator::create($request->only('comparator'));

			if ($comparator) {
				return $this->returnSuccessMessage('comparator', new ComparatorResource($comparator));
			}

			// Send error if comparator is not created
			return $this->returnError('comparator', 503, 'create');
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
		$comparator = Comparator::find($id);
		if ($comparator) {
			return $this->returnSuccessMessage('comparator', new ComparatorResource($comparator));
		}

		// Send error if comparator does not exist
		return $this->returnError('comparator', 404, 'show');
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
			'comparator' => 'filled|max:191'
		]);

		try {
			if (!$this->hasPermission()) {
				return $this->returnError('comparator', 403, 'update');
			}

			$comparator = Comparator::find($id);

			// Send error if comparator does not exist
			if (!$comparator) {
				return $this->returnError('comparator', 404, 'update');
			}

			// Update comparator
			if ($comparator->fill($request->only('comparator'))->save()) {
				return $this->returnSuccessMessage('comparator', new ComparatorResource($comparator));
			}

			// Send error if there is an error on update
			return $this->returnError('comparator', 503, 'update');
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
				return $this->returnError('comparator', 403, 'delete');
			}

			$comparator = Comparator::find($id);

			// Send error if comparator does not exist
			if (!$comparator) {
				return $this->returnError('comparator', 404, 'delete');
			}

			// Delete comparator
			if ($comparator->delete()) {
				return $this->returnSuccessMessage('message', 'Comparator has been deleted successfully.');
			}

			// Send error if there is an error on update
			return $this->returnError('comparator', 503, 'delete');
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
