<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Question;
use App\Answer;

class AnswerController extends Controller
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
     * @return \Illuminate\Http\Response
     */
    public function index($question_id, Request $request)
    {
        $answer = Question::find($question_id)->answer()->get();
        return response()->json(['status' => 'success','result' => $answer]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store($question_id, Request $request)
    {
        $this->validate($request, [
            'answer' => 'required',
            'order' => 'required'
        ]);
        if(Question::find($question_id)->answer()->Create($request->all())){
            return response()->json(['status' => 'success']);
        }else{
            return response()->json(['status' => 'fail']);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
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
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($question_id, $id)
    {
        $answer = Question::find($question_id)->answer()->where('id', $id)->get();
        return view('answer.editanswer',['answers' => $answer]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update($question_id, Request $request, $id)
    {
        $this->validate($request, [
            'answer' => 'filled',
            'order' => 'filled'
        ]);
        $answer = Question::find($question_id)->answer()->find($id);
        if($answer->fill($request->all())->save()){
            return response()->json(['status' => 'success']);
        }
        return response()->json(['status' => 'failed']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($question_id, $id)
    {
        if(Question::find($question_id)->answer()->destroy($id)){
            return response()->json(['status' => 'success']);
        }
    }
}
