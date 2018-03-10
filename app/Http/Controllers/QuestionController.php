<?php

namespace App\Http\Controllers;

use App\Models\Section;
use Illuminate\Http\Request;

class QuestionController extends Controller
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
     * @param  int $section_id
     * @return \Illuminate\Http\JsonResponse
     */
    public function index($section_id)
    {
        $questions = Section::find($section_id)->questions()->get();
        return response()->json([
            'status' => 'success',
            'questions' => $questions
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  int $section_id
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store($section_id, Request $request)
    {
        $this->validate($request, [
            'question' => 'required',
            'order' => 'required'
        ]);

        $question = Section::find($section_id)->questions()->create($request->all());
        if ($question) {
            return response()->json([
                'status' => 'success',
                'question' => $question
            ], 200);
        }
        return response()->json([
            'status' => 'fail',
            'message' => $this->generateErrorMessage('question', 503, 'store')
        ], 503);
    }

    /**
     * Display the specified resource.
     *
     * @param  int $section_id
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show($section_id, $id)
    {
        $question = Section::find($section_id)->questions()->where('id', $id)->first();
        if ($question) {
            return response()->json([
                'status' => 'success',
                'question' => $question
            ], 200);
        }
        return response()->json([
            'status' => 'fail',
            'message' => $this->generateErrorMessage('question', 404, 'show')
        ], 404);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int $section_id
     * @param  int $id
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function update($section_id, $id, Request $request)
    {
        $this->validate($request, [
            'question' => 'filled',
            'order' => 'filled'
        ]);

        $question = Section::find($section_id)->questions()->where('id', $id)->first();
        if (!$question) {
            return response()->json([
                'status' => 'fail',
                'message' => $this->generateErrorMessage('question', 404, 'update')
            ], 404);
        }
        if ($question->fill($request->all())->save()) {
            return response()->json([
                'status' => 'success',
                'question' => $question
            ], 200);
        }
        return response()->json([
            'status' => 'fail',
            'message' => $this->generateErrorMessage('question', 503, 'update')
        ], 404);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $section_id
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($section_id, $id)
    {
        if (Section::find($section_id)->questions()->destroy($id)) {
            return response()->json(['status' => 'success'], 200);
        }
        return response()->json([
            'status' => 'fail',
            'message' => $this->generateErrorMessage('question', 503, 'delete')
        ], 503);
    }
}
