<?php

namespace App\Http\Controllers;

use Auth;
use Exception;
use App\Models\Team;
use App\Models\Form;
use App\Http\Resources\SubmissionResource;
use Illuminate\Http\Request;

class SubmissionController extends Controller
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
	 * @param  int $form_id
	 *
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function index($form_id)
	{
		$submissions = Auth::user()->submissions()->where('form_id', $form_id)->get();

		return $this->returnSuccessMessage('submissions', SubmissionResource::collection($submissions));
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @param  int $form_id
	 * @param  \Illuminate\Http\Request $request
	 *
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function store($form_id, Request $request)
	{
		$this->validate($request, [
			'team_id' => 'nullable|integer|min:1',
			'progress' => 'filled|integer|min:0',
			'period_start' => 'nullable|date',
			'period_end' => 'nullable|date'
		]);

		try {
			$form = Form::find($form_id);

			// Send error if form does not exist
			if (!$form) {
				return $this->returnError('form', 404, 'create submission');
			}

			$team_id = $request->input('team_id', null);
			if ($team_id) {
				// Send error if team does not exist
				if (!Team::find($team_id)) {
					return $this->returnError('team', 404, 'create submission');
				}
			}

			// Create submission
			$submission = $form->submissions()->create([
				'user_id' => Auth::user()->id,
				'team_id' => $team_id,
				'progress' => $request->input('progress', 0),
				'period_start' => $request->input('period_start', null),
				'period_end' => $request->input('period_end', null)
			]);

			if ($submission) {
				return $this->returnSuccessMessage('submission', new SubmissionResource($submission));
			}

			// Send error if submission is not created
			return $this->returnError('submission', 503, 'create');
		} catch (Exception $e) {
			// Send error
			return $this->returnErrorMessage(503, $e->getMessage());
		}
	}

	/**
	 * Display the specified resource.
	 *
	 * @param  int $form_id
	 * @param  int $id
	 *
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function show($form_id, $id)
	{
		$form = Form::find($form_id);

		// Send error if form does not exist
		if (!$form) {
			return $this->returnError('form', 404, 'show submission');
		}

		$submission = $form->submissions()->find($id);
		if ($submission) {
			$user = Auth::user();
			if ($user->role->name != 'Super Admin' || $submission->user_id != $user->id) {
				return $this->returnError('submission', 403, 'see');
			}

			return $this->returnSuccessMessage('submission', new SubmissionResource($submission));
		}

		// Send error if submission does not exist
		return $this->returnError('submission', 404, 'show');
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  int $form_id
	 * @param  int $id
	 * @param  \Illuminate\Http\Request $request
	 *
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function update($form_id, $id, Request $request)
	{
		$this->validate($request, [
			'progress' => 'filled|integer|min:0',
			'period_start' => 'nullable|date',
			'period_end' => 'nullable|date'
		]);

		try {
			$form = Form::find($form_id);

			// Send error if form does not exist
			if (!$form) {
				return $this->returnError('form', 404, 'update submission');
			}

			$submission = $form->submissions()->where([
				'id' => $id,
				'user_id' => Auth::user()->id
			])->first();

			// Send error if submission does not exist
			if (!$submission) {
				return $this->returnError('submission', 404, 'update');
			}

			// Update submission
			if ($submission->fill($request->only('progress', 'period_start', 'period_end'))->save()) {
				return $this->returnSuccessMessage('submission', new SubmissionResource($submission));
			}

			// Send error if there is an error on update
			return $this->returnError('submission', 503, 'update');
		} catch (Exception $e) {
			// Send error
			return $this->returnErrorMessage(503, $e->getMessage());
		}
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int $form_id
	 * @param  int $id
	 *
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function destroy($form_id, $id)
	{
		try {
			$form = Form::find($form_id);

			// Send error if form does not exist
			if (!$form) {
				return $this->returnError('form', 404, 'delete submission');
			}

			$submission = $form->submissions()->where([
				'id' => $id,
				'user_id' => Auth::user()->id
			])->first();

			// Send error if submission does not exist
			if (!$submission) {
				return $this->returnError('submission', 404, 'delete');
			}

			if ($submission->delete()) {
				return $this->returnSuccessMessage('message', 'Submission has been deleted successfully.');
			}

			// Send error if there is an error on update
			return $this->returnError('submission', 503, 'delete');
		} catch (Exception $e) {
			// Send error
			return $this->returnErrorMessage(503, $e->getMessage());
		}
	}
}
