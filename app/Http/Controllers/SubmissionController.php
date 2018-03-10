<?php

namespace App\Http\Controllers;

use Auth;
use App\Models\Form;
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
        if (Auth::user()->role == "SuperAdmin") {
            $submissions = Auth::user()->submissions()->where('form_id', $form_id)->get();
        } else {
            $submissions = Form::find($form_id)->submissions()->get();
        }
        return response()->json([
            'status' => 'success',
            'submissions' => $submissions
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  int $form_id
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store($form_id, Request $request)
    {
        $this->validate($request, [
            'form_id' => 'required'
        ]);

        $submission = Auth::user()->submissions()->create([
            'form_id' => $form_id,
            'team_id' => $request->input('team_id'. null),
            'period_start' => $request->input('period_start'. null),
            'period_end' => $request->input('period_end'. null)
        ]);
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
     * @param  int $form_id
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show($form_id, $id)
    {
        $submission = Form::find($form_id)->submissions()->where('id', $id)->first();
        if ($submission) {
            $user = Auth::user();
            if ($user->role != "SuperAdmin" || $submission->user_id != $user->id) {
                return response()->json([
                    'status' => 'fail',
                    'message' => 'You do not have permission to see this submission.'
                ], 404);
            }

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
     * @param  int $form_id
     * @param  \Illuminate\Http\Request $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function update($form_id, Request $request, $id)
    {
        $this->validate($request, [
            'form_id' => 'filled'
        ]);

        $submission = Form::find($form_id)->submissions()->where('id', $id)->first();
        if (!$submission) {
            return response()->json([
                'status' => 'fail',
                'message' => $this->generateErrorMessage('submission', 404, 'update')
            ], 404);
        }

        $user = Auth::user();
        if ($user->role != "SuperAdmin" || $submission->user_id != $user->id) {
            return response()->json([
                'status' => 'fail',
                'message' => 'You do not have permission to update this submission.'
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
        ], 503);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $form_id
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($form_id, $id)
    {
        $submission = Form::find($form_id)->submissions()->where('id', $id)->first();
        $user = Auth::user();
        if ($user->role != "SuperAdmin" || $submission->user_id != $user->id) {
            return response()->json([
                'status' => 'fail',
                'message' => 'You do not have permission to delete this submission.'
            ], 404);
        }

        if ($submission->delete()) {
            return response()->json(['status' => 'success'], 200);
        }
        return response()->json([
            'status' => 'fail',
            'message' => $this->generateErrorMessage('submission', 503, 'delete')
        ], 503);
    }
}
