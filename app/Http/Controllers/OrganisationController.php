<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Organisation;
use Auth;

class OrganisationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $organisation = Auth::user()->organisation()->get();
        return response()->json(['status' => 'success', 'result' => $organisation]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required'
        ]);

        if (Auth::user()->organisation()->Create($request->all())) {
            return response()->json(['status' => 'success']);
        }

        return response()->json(['status' => 'fail']);
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $organisation = Organisation::where('id', $id)->get();
        return response()->json($organisation);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $organisation = Organisation::where('id', $id)->get();
        return view('organisation.editOrganisation', ['organisations' => $organisation]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'name' => 'filled'
        ]);

        $organisation = Organisation::find($id);
        if ($organisation->fill($request->all())->save()) {
            return response()->json(['status' => 'success']);
        }

        return response()->json(['status' => 'fail']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if (Organisation::destroy($id)) {
            return response()->json(['status' => 'success']);
        }

        return response()->json(['status' => 'fail']);
    }
}
