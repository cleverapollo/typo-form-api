<?php

namespace App\Http\Controllers;

use Auth;
use Exception;
use App\Models\Team;
use App\Models\Form;
use App\Models\Status;
use App\Http\Resources\SubmissionResource;
use App\Notifications\InformedNotification;
use App\Exports\CSVExport;
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
	 * @throws \Illuminate\Validation\ValidationException
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
				'period_start' => $request->input('period_start', $form->period_start),
				'period_end' => $request->input('period_end', $form->period_end),
				'status_id' => Status::where('status', 'opened')->first()->id
			]);

			if ($submission) {
				// Send notification email
				if ($team_id) {
					foreach (Team::find($team_id)->users as $user) {
						if ($user->email) {
							$user->notify(new InformedNotification('Submission is created successfully.'));
						}
					}
				} else {
					if (Auth::user()->email) {
						Auth::user()->notify(new InformedNotification('Submission is created successfully.'));
					}
				}

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
	 * @throws \Illuminate\Validation\ValidationException
	 */
	public function update($form_id, $id, Request $request)
	{
		$this->validate($request, [
			'progress' => 'filled|integer|min:0',
			'period_start' => 'nullable|date',
			'period_end' => 'nullable|date',
			'status_id' => 'filled|integer|min:1'
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

			// Check whether the question type exists or not
			$status_id = $request->input('status_id', null);
			if ($status_id && !Status::find($status_id)) {
				return $this->returnError('status', 404, 'update submission');
			}

			// Update submission
			if ($submission->fill($request->only('progress', 'period_start', 'period_end', 'status_id'))->save()) {
				// Send notification email
				if ($status_id) {
					$admin_users = $this->applicationAdmins($form->application);
					foreach ($admin_users as $admin_user) {
						if ($admin_user->email) {
							$admin_user->notify(new InformedNotification('Submission status is updated successfully.'));
						}
					}
				}

				if ($submission->team) {
					foreach ($submission->team->users as $user) {
						if ($user->email) {
							$user->notify(new InformedNotification('Submission is updated successfully.'));
						}
					}
				} else {
					if ($submission->user->email) {
						$submission->user->notify(new InformedNotification('Submission is updated successfully.'));
					}
				}

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

			if ($submission->team) {
				$team = $submission->team;
			} else {
				$user = $submission->user;
			}

			if ($submission->delete()) {
				// Send email notification
				if ($team) {
					foreach ($team->users as $tuser) {
						if ($tuser->email) {
							$tuser->notify(new InformedNotification('Submission is deleted successfully.'));
						}
					}
				} else {
					if ($user->email) {
						$user->notify(new InformedNotification('Submission is deleted successfully.'));
					}
				}

				return $this->returnSuccessMessage('message', 'Submission has been deleted successfully.');
			}

			// Send error if there is an error on update
			return $this->returnError('submission', 503, 'delete');
		} catch (Exception $e) {
			// Send error
			return $this->returnErrorMessage(503, $e->getMessage());
		}
	}

	/**
	 * Download csv file
	 *
	 * @return CSVExport
	 */
	public function export()
	{
		return new CSVExport();
	}

	/**
	 * Store submissions as csv file
	 *
	 * @return bool|\Illuminate\Foundation\Bus\PendingDispatch
	 */
	public function storeCSV()
	{
		return (new CSVExport)->store('submissions.csv');
	}
}
