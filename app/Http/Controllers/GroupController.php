<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Section;
use App\Models\Group;

class GroupController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param $section_id
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index($section_id, Request $request)
    {
        $group = Section::find($section_id)->group()->get();
        return response()->json(['status' => 'success', 'result' => $group]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param $section_id
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store($section_id, Request $request)
    {
        $this->validate($request, [
            'name' => 'required'
        ]);

        if (Section::find($section_id)->group()->Create($request->all())) {
            return response()->json(['status' => 'success']);
        }

        return response()->json(['status' => 'fail']);
    }

    /**
     * Display the specified resource.
     *
     * @param $section_id
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show($section_id, $id)
    {
        $group = Section::find($section_id)->group()->where('id', $id)->get();
        return response()->json($group);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param $section_id
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($section_id, $id)
    {
        $group = Section::find($section_id)->group()->where('id', $id)->get();
        return view('group.editGroup', ['groups' => $group]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param $section_id
     * @param  \Illuminate\Http\Request $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function update($section_id, Request $request, $id)
    {
        $this->validate($request, [
            'name' => 'filled'
        ]);

        $group = Section::find($section_id)->group()->find($id);
        if ($group->fill($request->all())->save()) {
            return response()->json(['status' => 'success']);
        }

        return response()->json(['status' => 'fail']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param $section_id
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($section_id, $id)
    {
        if (Section::find($section_id)->group()->destroy($id)) {
            return response()->json(['status' => 'success']);
        }

        return response()->json(['status' => 'fail']);
    }
}
