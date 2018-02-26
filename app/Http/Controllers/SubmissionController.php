<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Team;
use Auth;

class SubmissionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param $team_id
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index($team_id, Request $request)
    {
        $submission = Auth::user()->submission()->where('team_id', $team_id)->get();
        return response()->json(['status' => 'success', 'result' => $submission]);
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

        if (Auth::user()->submission()->Create(['form_id' => $request->form_id,
            'team_id' => $team_id])) {
            return response()->json(['status' => 'success']);
        }

        return response()->json(['status' => 'fail']);
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
        return response()->json($submission);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param $team_id
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($team_id, $id)
    {
        $submission = Auth::user()->submission()->where('id', $id)->where('team_id', $team_id)->get();
        return view('submission.editSubmission', ['submissions' => $submission]);
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

        $team = Auth::user()->submission()->find($id);
        if ($team->fill($request->all())->save()) {
            return response()->json(['status' => 'success']);
        }

        return response()->json(['status' => 'fail']);
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
            return response()->json(['status' => 'success']);
        }

        return response()->json(['status' => 'fail']);
    }
}
