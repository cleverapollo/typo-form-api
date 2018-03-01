<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Question;

class AnswerController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param $question_id
     * @return \Illuminate\Http\JsonResponse
     */
    public function index($question_id)
    {
        $answer = Question::find($question_id)->answer()->get();
        return response()->json(['status' => 'success', 'result' => $answer]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param $question_id
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store($question_id, Request $request)
    {
        $this->validate($request, [
            'answer' => 'required',
            'order' => 'required'
        ]);

        if (Question::find($question_id)->answer()->Create($request->all())) {
            return response()->json(['status' => 'success'], 200);
        }

        return response()->json(['status' => 'fail']);
    }

    /**
     * Display the specified resource.
     *
     * @param $question_id
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show($question_id, $id)
    {
        $answer = Question::find($question_id)->answer()->where('id', $id)->get();
        return response()->json($answer);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param $question_id
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($question_id, $id)
    {
        $answer = Question::find($question_id)->answer()->where('id', $id)->get();
        return view('answer.editAnswer', ['answers' => $answer]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param $question_id
     * @param  \Illuminate\Http\Request $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function update($question_id, Request $request, $id)
    {
        $this->validate($request, [
            'answer' => 'filled',
            'order' => 'filled'
        ]);

        $answer = Question::find($question_id)->answer()->find($id);
        if ($answer->fill($request->all())->save()) {
            return response()->json(['status' => 'success'], 200);
        }

        return response()->json(['status' => 'fail']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param $question_id
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($question_id, $id)
    {
        if (Question::find($question_id)->answer()->destroy($id)) {
            return response()->json(['status' => 'success'], 200);
        }

        return response()->json(['status' => 'fail']);
    }
}
