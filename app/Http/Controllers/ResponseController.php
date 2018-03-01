<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Submission;

class ResponseController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param $submission_id
     * @return \Illuminate\Http\JsonResponse
     */
    public function index($submission_id)
    {
        $response = Submission::find($submission_id)->response()->get();
        return response()->json(['status' => 'success', 'result' => $response]);
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
            'answer_id' => 'required'
        ]);

        if (Submission::find($submission_id)->response()->Create($request->all())) {
            return response()->json(['status' => 'success'], 200);
        }

        return response()->json(['status' => 'fail']);
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
        return response()->json($response);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param $submission_id
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($submission_id, $id)
    {
        $response = Submission::find($submission_id)->response()->where('id', $id)->get();
        return view('response.editResponse', ['responses' => $response]);
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
            'answer_id' => 'filled'
        ]);

        $response = Submission::find($submission_id)->response()->find($id);
        if ($response->fill($request->all())->save()) {
            return response()->json(['status' => 'success'], 200);
        }

        return response()->json(['status' => 'fail']);
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

        return response()->json(['status' => 'fail']);
    }
}
