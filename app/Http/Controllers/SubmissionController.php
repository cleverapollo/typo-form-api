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
     * @param $organisation_id
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index($organisation_id, Request $request)
    {
        $submission = Auth::user()->submission()->where('organisation_id', $organisation_id)->get();
        return response()->json(['status' => 'success', 'result' => $submission]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param $organisation_id
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store($organisation_id, Request $request)
    {
        $this->validate($request, [
            'form_id' => 'required'
        ]);

        if (Auth::user()->submission()->Create(['form_id' => $request->form_id,
            'organisation_id' => $organisation_id])) {
            return response()->json(['status' => 'success']);
        }

        return response()->json(['status' => 'fail']);
    }

    /**
     * Display the specified resource.
     *
     * @param $organisation_id
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show($organisation_id, $id)
    {
        $submission = Auth::user()->submission()->where('id', $id)->where('organisation_id', $organisation_id)->get();
        return response()->json($submission);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param $organisation_id
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($organisation_id, $id)
    {
        $submission = Auth::user()->submission()->where('id', $id)->where('organisation_id', $organisation_id)->get();
        return view('submission.editSubmission', ['submissions' => $submission]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param $organisation_id
     * @param  \Illuminate\Http\Request $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function update($organisation_id, Request $request, $id)
    {
        $this->validate($request, [
            'form_id' => 'filled'
        ]);

        $organisation = Auth::user()->submission()->find($id);
        if ($organisation->fill($request->all())->save()) {
            return response()->json(['status' => 'success']);
        }

        return response()->json(['status' => 'fail']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param $organisation_id
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($organisation_id, $id)
    {
        if (Auth::user()->submission()->destroy($id)) {
            return response()->json(['status' => 'success']);
        }

        return response()->json(['status' => 'fail']);
    }
}
