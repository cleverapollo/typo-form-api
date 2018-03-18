<?php

namespace App\Http\Controllers;

use App\Models\Submission;
use App\Http\Resources\ResponseResource;
use Illuminate\Http\Request;

class ResponseController extends Controller
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
     * @param  int $submission_id
     * @return \Illuminate\Http\JsonResponse
     */
    public function index($submission_id)
    {
        $responses = Submission::find($submission_id)->responses()->get();
	    return $this->returnSuccessMessage('responses', ResponseResource::collection($responses));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  int $submission_id
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store($submission_id, Request $request)
    {
        $this->validate($request, [
            'response' => 'required',
            'answer_id' => 'required'
        ]);

	    $submission = Submission::find($submission_id);

	    // Send error if submission does not exist
	    if (!$submission) {
		    return $this->returnErrorMessage('submission', 404, 'create response');
	    }

        $response = $submission->responses()->create($request->only('response', 'answer_id'));
        if ($response) {
	        return $this->returnSuccessMessage('response', new ResponseResource($response));
        }

	    // Send error if response is not created
	    return $this->returnErrorMessage('response', 503, 'create');
    }

    /**
     * Display the specified resource.
     *
     * @param  int $submission_id
     * @param  int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($submission_id, $id)
    {
	    $submission = Submission::find($submission_id);

	    // Send error if submission does not exist
	    if (!$submission) {
		    return $this->returnErrorMessage('submission', 404, 'show response');
	    }

        $response = $submission->responses()->where('id', $id)->first();
        if ($response) {
	        return $this->returnSuccessMessage('response', new ResponseResource($response));
        }

	    // Send error if response does not exist
	    return $this->returnErrorMessage('response', 404, 'show');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int $submission_id
     * @param  int $id
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function update($submission_id, $id, Request $request)
    {
        $this->validate($request, [
            'response' => 'filled',
            'answer_id' => 'filled'
        ]);

	    $submission = Submission::find($submission_id);

	    // Send error if submission does not exist
	    if (!$submission) {
		    return $this->returnErrorMessage('submission', 404, 'show response');
	    }

        $response = $submission->responses()->where('id', $id)->first();
	    // Send error if response does not exist
	    if (!$response) {
		    return $this->returnErrorMessage('response', 404, 'update');
	    }

        $newResponse = $response->fill($request->only('response', 'answer_id'));

        if ($submission->responses()->where('id', $id)->delete()) {
        	$new = $submission->responses()->create([
        		'response' => $newResponse->response,
		        'answer_id' => $newResponse->answer_id
	        ]);

            if ($new) {
	            return $this->returnSuccessMessage('response', new ResponseResource($new));
            }
        }

	    // Send error if there is an error on update
	    return $this->returnErrorMessage('response', 503, 'update');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $submission_id
     * @param  int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($submission_id, $id)
    {
	    $submission = Submission::find($submission_id);

	    // Send error if submission does not exist
	    if (!$submission) {
		    return $this->returnErrorMessage('submission', 404, 'show response');
	    }

	    $response = $submission->responses()->where('id', $id)->first();

	    // Send error if response does not exist
	    if (!$response) {
		    return $this->returnErrorMessage('response', 404, 'delete');
	    }

	    if ($response->delete()) {
	        return $this->returnSuccessMessage('message', 'Response has been deleted successfully.');
        }

	    // Send error if there is an error on update
	    return $this->returnErrorMessage('response', 503, 'delete');
    }
}
