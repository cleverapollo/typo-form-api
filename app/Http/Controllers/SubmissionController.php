<?php

namespace App\Http\Controllers;

use Auth;
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
     * @param $team_id
     * @return \Illuminate\Http\JsonResponse
     */
    public function index($team_id)
    {
        $submissions = Auth::user()->submission()->where('team_id', $team_id)->get();
        return response()->json([
            'status' => 'success',
            'submissions' => $submissions
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param $team_id
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store($team_id, Request $request)
    {
        $this->validate($request, [
            'form_id' => 'required'
        ]);

        $submission = Auth::user()->submission()->Create(['form_id' => $request->form_id, 'team_id' => $team_id]);
        if ($submission) {
            return response()->json([
                'status' => 'success',
                'submission' => $submission
            ], 200);
        }
        return response()->json([
            'status' => 'fail',
            'message' => $this->generateErrorMessage('submission', 503, 'store')
        ], 503);
    }

    /**
     * Display the specified resource.
     *
     * @param $team_id
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show($team_id, $id)
    {
        $submission = Auth::user()->submission()->where('id', $id)->where('team_id', $team_id)->get();
        if ($submission) {
            return response()->json([
                'status' => 'success',
                'submission' => $submission
            ], 200);
        }
        return response()->json([
            'status' => 'fail',
            'message' => $this->generateErrorMessage('submission', 404, 'show')
        ], 404);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param $team_id
     * @param  \Illuminate\Http\Request $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function update($team_id, Request $request, $id)
    {
        $this->validate($request, [
            'form_id' => 'filled'
        ]);

        $submission = Auth::user()->submission()->find($id);
        if (!$submission) {
            return response()->json([
                'status' => 'fail',
                'message' => $this->generateErrorMessage('submission', 404, 'update')
            ], 404);
        }
        if ($submission->fill($request->all())->save()) {
            return response()->json([
                'status' => 'success',
                'submission' => $submission
            ], 200);
        }
        return response()->json([
            'status' => 'fail',
            'message' => $this->generateErrorMessage('submission', 503, 'update')
        ], 404);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param $team_id
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($team_id, $id)
    {
        if (Auth::user()->submission()->destroy($id)) {
            return response()->json(['status' => 'success'], 200);
        }
        return response()->json([
            'status' => 'fail',
            'message' => $this->generateErrorMessage('submission', 503, 'delete')
        ], 503);
    }
}
