<?php

namespace App\Http\Controllers;

use App\Models\Section;
use Illuminate\Http\Request;

class GroupController extends Controller
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
        $groups = Section::find($section_id)->groups()->get();
        return response()->json([
            'status' => 'success',
            'groups' => $groups
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
            'name' => 'required|max:191'
        ]);

        $group = Section::find($section_id)->groups()->create($request->all());
        if ($group) {
            return response()->json([
                'status' => 'success',
                'group' => $group
            ], 200);
        }
        return response()->json([
            'status' => 'fail',
            'message' => $this->generateErrorMessage('group', 503, 'store')
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
        $group = Section::find($section_id)->groups()->where('id', $id)->first();
        if ($group) {
            return response()->json([
                'status' => 'success',
                'group' => $group
            ], 200);
        }
        return response()->json([
            'status' => 'fail',
            'message' => $this->generateErrorMessage('group', 404, 'show')
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
            'name' => 'filled'
        ]);

        $group = Section::find($section_id)->groups()->where('id', $id)->first();
        if (!$group) {
            return response()->json([
                'status' => 'fail',
                'message' => $this->generateErrorMessage('group', 404, 'update')
            ], 404);
        }
        if ($group->fill($request->all())->save()) {
            return response()->json([
                'status' => 'success',
                'group' => $group
            ], 200);
        }
        return response()->json([
            'status' => 'fail',
            'message' => $this->generateErrorMessage('group', 503, 'update')
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
        if (Section::find($section_id)->groups()->destroy($id)) {
            return response()->json(['status' => 'success'], 200);
        }
        return response()->json([
            'status' => 'fail',
            'message' => $this->generateErrorMessage('group', 503, 'delete')
        ], 503);
    }
}
