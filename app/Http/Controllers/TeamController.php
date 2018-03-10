<?php

namespace App\Http\Controllers;

use Auth;
use App\Models\Team;
use App\Models\Application;
use Illuminate\Http\Request;

class TeamController extends Controller
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
        $teams = Application::find($application_id)->teams()->get();
        return response()->json([
            'status' => 'success',
            'teams' => $teams
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

        $user = Auth::user();
        $team = $user->teams()->create([
            'name' => $request->input('name'),
            'description' => $request->input('description', null),
            'application_id' => $application_id
        ]);

        if ($team) {
            // Send invitation
            $invitations = $request->input('invitations', []);
            $this->sendInvitation('team', $team, $user, $invitations);

            return response()->json([
                'status' => 'success',
                'team' => $team
            ], 200);
        }
        return response()->json([
            'status' => 'fail',
            'message' => $this->generateErrorMessage('team', 503, 'store')
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
        $team = Application::find($application_id)->teams()->where('id', $id)->first();
        if ($team) {
            return response()->json([
                'status' => 'success',
                'team' => $team
            ], 200);
        }
        return response()->json([
            'status' => 'fail',
            'message' => $this->generateErrorMessage('team', 404, 'show')
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

        $team = Application::find($application_id)->teams()->where('id', $id)->first();
        if (!$team) {
            return response()->json([
                'status' => 'fail',
                'message' => $this->generateErrorMessage('team', 404, 'update')
            ], 404);
        }
        if ($team->fill($request->all())->save()) {
            return response()->json([
                'status' => 'success',
                'team' => $team
            ], 200);
        }
        return response()->json([
            'status' => 'fail',
            'message' => $this->generateErrorMessage('team', 503, 'update')
        ], 503);
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
        if (Application::find($application_id)->teams()->destroy($id)) {
            return response()->json(['status' => 'success'], 200);
        }

        return response()->json([
            'status' => 'fail',
            'message' => $this->generateErrorMessage('team', 503, 'delete')
        ], 503);
    }
}
