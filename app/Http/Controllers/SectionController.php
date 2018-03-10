<?php

namespace App\Http\Controllers;

use App\Models\Form;
use Illuminate\Http\Request;

class SectionController extends Controller
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
        $sections = Form::find($form_id)->sections()->get();
        return response()->json([
            'status' => 'success',
            'sections' => $sections
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
            'name' => 'required|max:191',
            'order' => 'required'
        ]);

        $section = Form::find($form_id)->sections()->create($request->all());
        if ($section) {
            return response()->json([
                'status' => 'success',
                'section' => $section
            ], 200);
        }
        return response()->json([
            'status' => 'fail',
            'message' => $this->generateErrorMessage('section', 503, 'store')
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
        $section = Form::find($form_id)->sections()->where('id', $id)->first();
        if ($section) {
            return response()->json([
                'status' => 'success',
                'section' => $section
            ], 200);
        }
        return response()->json([
            'status' => 'fail',
            'message' => $this->generateErrorMessage('section', 404, 'show')
        ], 404);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int $form_id
     * @param  int $id
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function update($form_id, $id, Request $request)
    {
        $this->validate($request, [
            'name' => 'filled',
            'order' => 'filled'
        ]);

        $section = Form::find($form_id)->sections()->where('id', $id)->first();
        if (!$section) {
            return response()->json([
                'status' => 'fail',
                'message' => $this->generateErrorMessage('section', 404, 'update')
            ], 404);
        }
        if ($section->fill($request->all())->save()) {
            return response()->json([
                'status' => 'success',
                'section' => $section
            ], 200);
        }
        return response()->json([
            'status' => 'fail',
            'message' => $this->generateErrorMessage('section', 503, 'update')
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
        if (Form::find($form_id)->sections()->where('id', $id)->delete()) {
            return response()->json(['status' => 'success'], 200);
        }
        return response()->json([
            'status' => 'fail',
            'message' => $this->generateErrorMessage('section', 503, 'delete')
        ], 503);
    }
}
