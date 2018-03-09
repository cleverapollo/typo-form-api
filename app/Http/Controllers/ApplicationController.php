<?php

namespace App\Http\Controllers;

use Auth;
use App\Models\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class ApplicationController extends Controller
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
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $applications = Auth::user()->application()->get();
        return response()->json([
            'status' => 'success',
            'applications' => $applications
        ], 200);
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
            'name' => 'required|max:191'
        ]);

        $user = Auth::user();
        $application = $user->application()->Create($request->only(['name']));
        if ($application) {
            // Send invitation
            $invitations = $request->input('invitations', []);
            $this->sendInvitation('application', $application, $user, $invitations);

            return response()->json([
                'status' => 'success',
                'application' => $application
            ], 200);
        }
        return response()->json([
            'status' => 'fail',
            'message' => $this->generateErrorMessage('application', 503, 'store')
        ], 503);
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $application = Application::where('id', $id)->get();
        if ($application) {
            return response()->json([
                'status' => 'success',
                'application' => $application
            ], 200);
        }
        return response()->json([
            'status' => 'fail',
            'message' => $this->generateErrorMessage('application', 404, 'show')
        ], 404);
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

        $application = Application::find($id);
        if (!$application) {
            return response()->json([
                'status' => 'fail',
                'message' => $this->generateErrorMessage('application', 404, 'update')
            ], 404);
        }
        if ($application->fill($request->all())->save()) {
            return response()->json([
                'status' => 'success',
                'application' => $application
            ], 200);
        }
        return response()->json([
            'status' => 'fail',
            'message' => $this->generateErrorMessage('application', 503, 'update')
        ], 404);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if (Application::destroy($id)) {
            return response()->json(['status' => 'success'], 200);
        }
        return response()->json([
            'status' => 'fail',
            'message' => $this->generateErrorMessage('application', 503, 'delete')
        ], 503);
    }
}
