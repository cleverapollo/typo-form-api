<?php

namespace App\Http\Controllers;

use App\Models\Submission;
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
     * @param $submission_id
     * @return \Illuminate\Http\JsonResponse
     */
    public function index($submission_id)
    {
        $responses = Submission::find($submission_id)->response()->get();
        return response()->json([
            'status' => 'success',
            'responses' => $responses
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param $submission_id
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store($submission_id, Request $request)
    {
        $this->validate($request, [
            'response' => 'required',
            'response_id' => 'required'
        ]);

        $response = Submission::find($submission_id)->response()->Create($request->all());
        if ($response) {
            return response()->json([
                'status' => 'success',
                'response' => $response
            ], 200);
        }
        return response()->json([
            'status' => 'fail',
            'message' => $this->generateErrorMessage('response', 503, 'store')
        ], 503);
    }

    /**
     * Display the specified resource.
     *
     * @param $submission_id
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show($submission_id, $id)
    {
        $response = Submission::find($submission_id)->response()->where('id', $id)->get();
        if ($response) {
            return response()->json([
                'status' => 'success',
                'response' => $response
            ], 200);
        }
        return response()->json([
            'status' => 'fail',
            'message' => $this->generateErrorMessage('response', 404, 'show')
        ], 404);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param $submission_id
     * @param  \Illuminate\Http\Request $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function update($submission_id, Request $request, $id)
    {
        $this->validate($request, [
            'response' => 'filled',
            'response_id' => 'filled'
        ]);

        $response = Submission::find($submission_id)->response()->find($id);
        if (!$response) {
            return response()->json([
                'status' => 'fail',
                'message' => $this->generateErrorMessage('response', 404, 'update')
            ], 404);
        }
        if ($response->fill($request->all())->save()) {
            return response()->json([
                'status' => 'success',
                'response' => $response
            ], 200);
        }
        return response()->json([
            'status' => 'fail',
            'message' => $this->generateErrorMessage('response', 503, 'update')
        ], 404);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param $submission_id
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($submission_id, $id)
    {
        if (Submission::find($submission_id)->response()->destroy($id)) {
            return response()->json(['status' => 'success'], 200);
        }
        return response()->json([
            'status' => 'fail',
            'message' => $this->generateErrorMessage('response', 503, 'delete')
        ], 503);
    }
}
