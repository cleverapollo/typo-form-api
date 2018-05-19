<?php

namespace App\Http\Controllers;

use Auth;
use Exception;
use App\Http\Resources\ApplicationEmailResource;
use Illuminate\Http\Request;

class ApplicationEmailController extends Controller
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
	 * @param  string $application_slug
	 *
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function index($application_slug)
	{
		$application = Auth::user()->applications()->where('slug', $application_slug)->first();

		// Send error if application does not exist
		if (!$application) {
			return $this->returnApplicationNameError();
		}

		return $this->returnSuccessMessage('application_emails', ApplicationEmailResource::collection($application->applicationEmails()->get()));
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @param  string $application_slug
	 * @param  \Illuminate\Http\Request $request
	 *
	 * @return \Illuminate\Http\JsonResponse
	 * @throws \Illuminate\Validation\ValidationException
	 */
	public function store($application_slug, Request $request)
	{
		$this->validate($request, [
			'recipients' => 'required|max:191',
			'subject' => 'required|max:191',
			'body' => 'required'
		]);

		try {
			$application = Auth::user()->applications()->where('slug', $application_slug)->first();

			// Send error if application does not exist
			if (!$application) {
				return $this->returnApplicationNameError();
			}

			// Create application email
			$application_email = $application->applicationEmails()->create($request->only('recipients', 'subject', 'body'));

			if ($application_email) {
				return $this->returnSuccessMessage('application_email', new ApplicationEmailResource($application_email));
			}

			// Send error if application email is not created
			return $this->returnError('application email', 503, 'create');
		} catch (Exception $e) {
			// Send error
			return $this->returnErrorMessage(503, $e->getMessage());
		}
	}

	/**
	 * Display the specified resource.
	 *
	 * @param  string $application_slug
	 * @param  int $id
	 *
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function show($application_slug, $id)
	{
		$application = Auth::user()->applications()->where('slug', $application_slug)->first();

		// Send error if application does not exist
		if (!$application) {
			return $this->returnApplicationNameError();
		}

		$application_email = $application->applicationEmails()->find($id);
		if ($application_email) {
			return $this->returnSuccessMessage('application_email', new ApplicationEmailResource($application_email));
		}

		// Send error if application email does not exist
		return $this->returnError('application email', 404, 'show');
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  string $application_slug
	 * @param  int $id
	 * @param  \Illuminate\Http\Request $request
	 *
	 * @return \Illuminate\Http\JsonResponse
	 * @throws \Illuminate\Validation\ValidationException
	 */
	public function update($application_slug, $id, Request $request)
	{
		$this->validate($request, [
			'recipients' => 'filled|max:191',
			'subject' => 'filled|max:191',
			'body' => 'filled'
		]);

		try {
			$application = Auth::user()->applications()->where('slug', $application_slug)->first();

			// Send error if application does not exist
			if (!$application) {
				return $this->returnApplicationNameError();
			}

			$application_email = $application->applicationEmails()->find($id);

			// Send error if application email does not exist
			if (!$application_email) {
				return $this->returnError('application email', 404, 'update');
			}

			// Update application email
			if ($application_email->fill($request->only('recipients', 'subject', 'body'))->save()) {
				return $this->returnSuccessMessage('application_email', new ApplicationEmailResource($application_email));
			}

			// Send error if there is an error on update
			return $this->returnError('application email', 503, 'update');
		} catch (Exception $e) {
			// Send error
			return $this->returnErrorMessage(503, $e->getMessage());
		}
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  string $application_slug
	 * @param  int $id
	 *
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function destroy($application_slug, $id)
	{
		try {
			$application = Auth::user()->applications()->where('slug', $application_slug)->first();

			// Send error if application does not exist
			if (!$application) {
				return $this->returnApplicationNameError();
			}

			$application_email = $application->applicationEmails()->find($id);

			// Send error if application email does not exist
			if (!$application_email) {
				return $this->returnError('application email', 404, 'delete');
			}

			if ($application_email->delete()) {
				return $this->returnSuccessMessage('message', 'Application Email has been deleted successfully.');
			}

			// Send error if there is an error on delete
			return $this->returnError('application email', 503, 'delete');
		} catch (Exception $e) {
			// Send error
			return $this->returnErrorMessage(503, $e->getMessage());
		}
	}
}
