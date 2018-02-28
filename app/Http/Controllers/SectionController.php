<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Section;
use App\Models\Form;

class SectionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param $form_id
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index($form_id, Request $request)
    {
        $section = Form::find($form_id)->section()->get();
        return response()->json(['status' => 'success', 'result' => $section]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param $form_id
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store($form_id, Request $request)
    {
        $this->validate($request, [
            'name' => 'required|max:255',
            'order' => 'required'
        ]);

        if (Form::find($form_id)->section()->Create($request->all())) {
            return response()->json(['status' => 'success']);
        }

        return response()->json(['status' => 'fail']);
    }

    /**
     * Display the specified resource.
     *
     * @param $form_id
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show($form_id, $id)
    {
        $section = Form::find($form_id)->section()->where('id', $id)->get();
        return response()->json($section);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param $form_id
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($form_id, $id)
    {
        $section = Form::find($form_id)->section()->where('id', $id)->get();
        return view('section.editSection', ['sections' => $section]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param $form_id
     * @param  \Illuminate\Http\Request $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function update($form_id, Request $request, $id)
    {
        $this->validate($request, [
            'name' => 'filled',
            'order' => 'filled'
        ]);

        $section = Form::find($form_id)->section()->find($id);
        if ($section->fill($request->all())->save()) {
            return response()->json(['status' => 'success']);
        }

        return response()->json(['status' => 'fail']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param $form_id
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($form_id, $id)
    {
        if (Form::find($form_id)->section()->destroy($id)) {
            return response()->json(['status' => 'success']);
        }

        return response()->json(['status' => 'fail']);
    }
}
