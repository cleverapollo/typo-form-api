<?php

namespace App\Http\Controllers;

use App\Models\Question;
use Illuminate\Http\Request;

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
     * @param  int $question_id
     * @return \Illuminate\Http\JsonResponse
     */
    public function index($question_id)
    {
        $answers = Question::find($question_id)->answers()->get();
        return response()->json([
            'status' => 'success',
            'answers' => $answers
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  int $question_id
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store($question_id, Request $request)
    {
        $this->validate($request, [
            'answer' => 'required',
            'order' => 'required'
        ]);

        $answer = Question::find($question_id)->answers()->create($request->all());
        if ($answer) {
            return response()->json([
                'status' => 'success',
                'answer' => $answer
            ], 200);
        }
        return response()->json([
            'status' => 'fail',
            'message' => $this->generateErrorMessage('answer', 503, 'store')
        ], 503);
    }

    /**
     * Display the specified resource.
     *
     * @param  int $question_id
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show($question_id, $id)
    {
        $answer = Question::find($question_id)->answers()->where('id', $id)->first();
        if ($answer) {
            return response()->json([
                'status' => 'success',
                'answer' => $answer
            ], 200);
        }
        return response()->json([
            'status' => 'fail',
            'message' => $this->generateErrorMessage('answer', 404, 'show')
        ], 404);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int $question_id
     * @param  int $id
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function update($question_id, $id, Request $request)
    {
        $this->validate($request, [
            'answer' => 'filled',
            'order' => 'filled'
        ]);

        $answer = Question::find($question_id)->answers()->where('id', $id)->first();
        if (!$answer) {
            return response()->json([
                'status' => 'fail',
                'message' => $this->generateErrorMessage('answer', 404, 'update')
            ], 404);
        }
        if ($answer->fill($request->all())->save()) {
            return response()->json([
                'status' => 'success',
                'answer' => $answer
            ], 200);
        }
        return response()->json([
            'status' => 'fail',
            'message' => $this->generateErrorMessage('answer', 503, 'update')
        ], 503);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $question_id
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($question_id, $id)
    {
        if (Question::find($question_id)->answers()->destroy($id)) {
            return response()->json(['status' => 'success'], 200);
        }
        return response()->json([
            'status' => 'fail',
            'message' => $this->generateErrorMessage('answer', 503, 'delete')
        ], 503);
    }
}
