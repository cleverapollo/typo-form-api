<?php

namespace App\Http\Controllers;

use Auth;
use Exception;
use App\Http\Resources\FormResource;
use Illuminate\Http\Request;

class FormController extends Controller
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
	 * @param  int $application_id
	 *
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function index($application_id)
	{
		$application = Auth::user()->applications()->where('application_id', $application_id)->first();

		// Send error if application does not exist
		if (!$application) {
			return $this->returnError('application', 404, 'get forms');
		}

		return $this->returnSuccessMessage('forms', FormResource::collection($application->forms()->get()));
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @param  int $application_id
	 * @param  \Illuminate\Http\Request $request
	 *
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function store($application_id, Request $request)
	{
		$this->validate($request, [
			'name' => 'required|max:191'
		]);

		try {
			$application = Auth::user()->applications()->where('application_id', $application_id)->first();

			// Send error if application does not exist
			if (!$application) {
				return $this->returnError('application', 404, 'create form');
			}

			// Create form
			$form = $application->forms()->create($request->only('name'));

			if ($form) {
				return $this->returnSuccessMessage('form', new FormResource($form));
			}

			// Send error if form is not created
			return $this->returnError('form', 503, 'create');
		} catch (Exception $e) {
			// Send error
			return $this->returnErrorMessage(503, $e->getMessage());
		}
	}

	/**
	 * Display the specified resource.
	 *
	 * @param  int $application_id
	 * @param  int $id
	 *
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function show($application_id, $id)
	{
		$application = Auth::user()->applications()->where('application_id', $application_id)->first();

		// Send error if application does not exist
		if (!$application) {
			return $this->returnError('application', 404, 'show form');
		}

		$form = $application->forms()->where('id', $id)->first();
		if ($form) {
			return $this->returnSuccessMessage('form', new FormResource($form));
		}

		// Send error if form does not exist
		return $this->returnError('form', 404, 'show');
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  int $application_id
	 * @param  int $id
	 * @param  \Illuminate\Http\Request $request
	 *
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function update($application_id, $id, Request $request)
	{
		$this->validate($request, [
			'name' => 'filled|max:191'
		]);

		try {
			$application = Auth::user()->applications()->where('application_id', $application_id)->first();

			// Send error if application does not exist
			if (!$application) {
				return $this->returnError('application', 404, 'update form');
			}

			$form = $application->forms()->where('id', $id)->first();

			// Send error if form does not exist
			if (!$form) {
				return $this->returnError('form', 404, 'update');
			}

			// Update form
			if ($form->fill($request->only('name'))->save()) {
				return $this->returnSuccessMessage('form', new FormResource($form));
			}

			// Send error if there is an error on update
			return $this->returnError('form', 503, 'update');
		} catch (Exception $e) {
			// Send error
			return $this->returnErrorMessage(503, $e->getMessage());
		}
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int $application_id
	 * @param  int $id
	 *
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function destroy($application_id, $id)
	{
		try {
			$application = Auth::user()->applications()->where('application_id', $application_id)->first();

			// Send error if application does not exist
			if (!$application) {
				return $this->returnError('application', 404, 'delete form');
			}

			if ($application->forms()->where('id', $id)->delete()) {
				return $this->returnSuccessMessage('message', 'Form has been deleted successfully.');
			}

			// Send error if there is an error on update
			return $this->returnError('form', 503, 'delete');
		} catch (Exception $e) {
			// Send error
			return $this->returnErrorMessage(503, $e->getMessage());
		}
	}
}
