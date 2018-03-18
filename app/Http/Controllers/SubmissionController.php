<?php

namespace App\Http\Controllers;

use Auth;
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
     * @return \Illuminate\Http\JsonResponse
     */
    public function store($form_id, Request $request)
    {
	    $form = Form::find($form_id);

	    // Send error if form does not exist
	    if (!$form) {
		    return $this->returnErrorMessage('form', 404, 'create submission');
	    }

	    // Create submission
        $submission = $form->submissions()->create([
            'user_id' => Auth::user()->id,
            'team_id' => $request->input('team_id', null),
            'period_start' => $request->input('period_start', null),
            'period_end' => $request->input('period_end', null)
        ]);

        if ($submission) {
        	// TODO: reconsider about getting the submission from table because of default value
	        return $this->returnSuccessMessage('submission', new SubmissionResource($submission));
        }

	    // Send error if submission is not created
	    return $this->returnErrorMessage('submission', 503, 'create');
    }

    /**
     * Display the specified resource.
     *
     * @param  int $form_id
     * @param  int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($form_id, $id)
    {
	    $form = Form::find($form_id);

	    // Send error if form does not exist
	    if (!$form) {
		    return $this->returnErrorMessage('form', 404, 'show submission');
	    }

        $submission = $form->submissions()->where('id', $id)->first();
        if ($submission) {
            $user = Auth::user();
            if ($user->role != "SuperAdmin" || $submission->user_id != $user->id) {
	            return $this->returnErrorMessage('submission', 403, 'see');
            }

	        return $this->returnSuccessMessage('submission', new SubmissionResource($submission));
        }

        // Send error if submission does not exist
	    return $this->returnErrorMessage('submission', 404, 'show');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int $form_id
     * @param  \Illuminate\Http\Request $request
     * @param  int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update($form_id, Request $request, $id)
    {
	    $form = Form::find($form_id);

	    // Send error if form does not exist
	    if (!$form) {
		    return $this->returnErrorMessage('form', 404, 'update submission');
	    }

        $submission = $form->submissions()->where([
        	'id' => $id,
	        'user_id' => Auth::user()->id
        ])->first();

	    // Send error if submission does not exist
        if (!$submission) {
	        return $this->returnErrorMessage('submission', 404, 'update');
        }

        // Update submission
        if ($submission->fill($request->only('period_start', 'period_end'))->save()) {
	        return $this->returnSuccessMessage('submission', new SubmissionResource($submission));
        }

	    // Send error if there is an error on update
	    return $this->returnErrorMessage('submission', 503, 'update');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $form_id
     * @param  int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($form_id, $id)
    {
	    $form = Form::find($form_id);

	    // Send error if form does not exist
	    if (!$form) {
		    return $this->returnErrorMessage('form', 404, 'delete submission');
	    }

	    $submission = $form->submissions()->where([
		    'id' => $id,
		    'user_id' => Auth::user()->id
	    ])->first();

	    // Send error if submission does not exist
	    if (!$submission) {
		    return $this->returnErrorMessage('submission', 404, 'delete');
	    }

        if ($submission->delete()) {
	        return $this->returnSuccessMessage('message', 'Submission has been deleted successfully.');
        }

	    // Send error if there is an error on update
	    return $this->returnErrorMessage('submission', 503, 'delete');
    }
}
