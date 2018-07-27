<?php

namespace App\Http\Controllers;

use Auth;
use Exception;
use App\Models\AnswerSort;
use App\Http\Resources\AnswerSortResource;
use Illuminate\Http\Request;

class AnswerSortController extends Controller
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
		$answer_sorts = AnswerSort::all();
		return $this->returnSuccessMessage('answer_sorts', AnswerSortResource::collection($answer_sorts));
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
			'sort' => 'required|max:191'
		]);

		try {
			if (!$this->hasPermission()) {
				return $this->returnError('answer_sort', 403, 'create');
			}

			// Create answer_sort
			$answer_sort = AnswerSort::create($request->only('answer_sort'));

			if ($answer_sort) {
				return $this->returnSuccessMessage('answer_sort', new AnswerSortResource($answer_sort));
			}

			// Send error if answer_sort is not created
			return $this->returnError('answer_sort', 503, 'create');
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
		$answer_sort = AnswerSort::find($id);
		if ($answer_sort) {
			return $this->returnSuccessMessage('answer_sort', new AnswerSortResource($answer_sort));
		}

		// Send error if answer_sort does not exist
		return $this->returnError('answer_sort', 404, 'show');
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
			'sort' => 'filled|max:191'
		]);

		try {
			if (!$this->hasPermission()) {
				return $this->returnError('answer_sort', 403, 'update');
			}

			$answer_sort = AnswerSort::find($id);

			// Send error if answer_sort does not exist
			if (!$answer_sort) {
				return $this->returnError('answer_sort', 404, 'update');
			}

			// Update answer_sort
			if ($answer_sort->fill($request->only('answer_sort'))->save()) {
				return $this->returnSuccessMessage('answer_sort', new AnswerSortResource($answer_sort));
			}

			// Send error if there is an error on update
			return $this->returnError('answer_sort', 503, 'update');
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
				return $this->returnError('answer_sort', 403, 'delete');
			}

			$answer_sort = AnswerSort::find($id);

			// Send error if answer_sort does not exist
			if (!$answer_sort) {
				return $this->returnError('answer_sort', 404, 'delete');
			}

			// Delete answer_sort
			if ($answer_sort->delete()) {
				return $this->returnSuccessMessage('message', 'AnswerSort has been deleted successfully.');
			}

			// Send error if there is an error on delete
			return $this->returnError('answer_sort', 503, 'delete');
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
