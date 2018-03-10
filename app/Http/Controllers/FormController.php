<?php

namespace App\Http\Controllers;

use App\Models\Application;
use Illuminate\Http\Request;

class FormController extends Controller
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
     * @param  int $application_id
     * @return \Illuminate\Http\JsonResponse
     */
    public function index($application_id)
    {
        $forms = Application::find($application_id)->forms()->get();
        return response()->json([
            'status' => 'success',
            'forms' => $forms
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  int $application_id
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store($application_id, Request $request)
    {
        $this->validate($request, [
            'name' => 'required|max:191'
        ]);

        $form = Application::find($application_id)->forms()->create($request->all());
        if ($form) {
            return response()->json([
                'status' => 'success',
                'form' => $form
            ], 200);
        }
        return response()->json([
            'status' => 'fail',
            'message' => $this->generateErrorMessage('form', 503, 'store')
        ], 503);
    }

    /**
     * Display the specified resource.
     *
     * @param  int $application_id
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show($application_id, $id)
    {
        $form = Application::find($application_id)->forms()->where('id', $id)->first();
        if ($form) {
            return response()->json([
                'status' => 'success',
                'form' => $form
            ], 200);
        }
        return response()->json([
            'status' => 'fail',
            'message' => $this->generateErrorMessage('form', 404, 'show')
        ], 404);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int $application_id
     * @param  int $id
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function update($application_id, $id, Request $request)
    {
        $this->validate($request, [
            'name' => 'filled'
        ]);

        $form = Application::find($application_id)->forms()->where('id', $id)->first();
        if (!$form) {
            return response()->json([
                'status' => 'fail',
                'message' => $this->generateErrorMessage('form', 404, 'update')
            ], 404);
        }
        if ($form->fill($request->all())->save()) {
            return response()->json([
                'status' => 'success',
                'form' => $form
            ], 200);
        }
        return response()->json([
            'status' => 'fail',
            'message' => $this->generateErrorMessage('form', 503, 'update')
        ], 404);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $application_id
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($application_id, $id)
    {
        if (Application::find($application_id)->forms()->destroy($id)) {
            return response()->json(['status' => 'success'], 200);
        }
        return response()->json([
            'status' => 'fail',
            'message' => $this->generateErrorMessage('form', 503, 'delete')
        ], 503);
    }
}
