<?php

namespace App\Http\Controllers;

use Auth;
use App\Models\Team;
use App\Models\Application;
use App\Models\TeamUser;
use App\Http\Resources\TeamResource;
use App\Http\Resources\UserResource;
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
        $this->middleware('auth:api', ['except' => ['invitation']]);
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
            'teams' => TeamResource::collection($teams)
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

        $share_token = base64_encode(str_random(40));
        while (!is_null(Team::where('share_token', $share_token)->first())) {
            $share_token = base64_encode(str_random(40));
        }

        $team = Auth::user()->teams()->create([
            'name' => $request->input('name'),
            'description' => $request->input('description', null),
            'application_id' => $application_id,
            'share_token' => $share_token
        ]);

        if ($team) {
            // Send invitation
            $invitations = $request->input('invitations', []);
            $this->sendInvitation('team', $team, $invitations);

            return response()->json([
                'status' => 'success',
                'team' => new TeamResource($team)
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
                'team' => new TeamResource($team)
            ], 200);
        }
        return response()->json([
            'status' => 'fail',
            'message' => $this->generateErrorMessage('team', 404, 'show')
        ], 404);
    }

    /**
     * Get users for the Team.
     *
     * @param  int $application_id
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function getUsers($application_id, $id)
    {
        $team = Application::find($application_id)->teams()->where('id', $id)->first();
        if ($team) {
            $users = $team->users()->get();
            return response()->json([
                'status' => 'success',
                'users' => UserResource::collection($users)
            ], 200);
        }

        return response()->json([
            'status' => 'fail',
            'message' => $this->generateErrorMessage('team', 404, 'show users')
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

        // Check whether user have permission to update
        $user = Auth::user();
        $role = TeamUser::where([
            'user_id' => $user->id,
            'team_id' => $team->id
        ])->value('role');
        if ($user->role != "SuperAdmin" && $role != "Admin") {
            return response()->json([
                'status' => 'fail',
                'message' => 'You do not have permission to update the team.'
            ], 403);
        }

        if ($team->fill($request->all())->save()) {
            return response()->json([
                'status' => 'success',
                'team' => new TeamResource($team)
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
        $user = Auth::user();
        $team = Application::find($application_id)->teams()->where('id', $id)->first();

        // Check whether user have permission to delete
        $role = TeamUser::where([
            'user_id' => $user->id,
            'team_id' => $team->id
        ])->value('role');
        if ($user->role != "SuperAdmin" && $role != "Admin") {
            return response()->json([
                'status' => 'fail',
                'message' => 'You do not have permission to delete team.'
            ], 403);
        }

        if ($team->delete()) {
            return response()->json(['status' => 'success'], 200);
        }

        return response()->json([
            'status' => 'fail',
            'message' => $this->generateErrorMessage('team', 503, 'delete')
        ], 503);
    }

    /**
     * Get Team invitation token
     *
     * @param $application_id
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getInvitationToken($application_id, $id)
    {
        $team = Application::find($application_id)->teams()->where('id', $id)->first();
        if ($team) {
            $user = Auth::user();

            // Check whether user have permission to get invitation token
            $role = TeamUser::where([
                'user_id' => $user->id,
                'team_id' => $team->id
            ])->value('role');
            if ($user->role != "SuperAdmin" && $role != "Admin") {
                return response()->json([
                    'status' => 'fail',
                    'message' => 'You do not have permission to get invitation token.'
                ], 403);
            }

            return response()->json([
                'status' => 'success',
                'shareToken' => $team->share_token
            ], 200);
        }
        return response()->json([
            'status' => 'fail',
            'message' => $this->generateErrorMessage('team', 404, 'get invitation token')
        ], 404);
    }

    /**
     * Accept invitation request
     *
     * @param $token
     */
    public function invitation($token)
    {
        $this->acceptInvitation('team', $token);
    }
}
