<?php

namespace App\Http\Controllers;

use Auth;
use App\User;
use App\Models\Application;
use App\Models\TeamInvitation;
use App\Models\UserTeam;
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
        if (Application::find($application_id)->teams()->where('id', $id)->delete()) {
            return response()->json(['status' => 'success'], 200);
        }

        return response()->json([
            'status' => 'fail',
            'message' => $this->generateErrorMessage('team', 503, 'delete')
        ], 503);
    }

    /**
     * Accept invitation request
     *
     * @param $token
     * @return \Illuminate\Http\JsonResponse
     */
    public function invitation($token)
    {
        $teamInvitation = TeamInvitation::where([
            'token' => $token
        ])->first();

        // Send error if token does not exist
        if (!$teamInvitation) {
            return response()->json([
                'status' => 'fail',
                'message' => 'Invalid token.'
            ], 404);
        }

        // Send request to create if the user is not registered
        $user = User::where('email', $teamInvitation->invitee)->first();
        if (!$user) {
            return response()->json([
                'status' => 'success',
                'message' => 'User need to create account.'
            ], 201);
        }

        if (UserTeam::create([
            'user_id' => $user->id,
            'team_id' => $teamInvitation->team_id,
            'role' => $teamInvitation->role
        ])) {
            $teamInvitation->token = null;
            $teamInvitation->status = 1;
            $teamInvitation->save();

            return response()->json([
                'status' => 'success',
                'message' => 'Invitation has been successfully accepted.'
            ], 200);
        };

        // Send error
        return response()->json([
            'status' => 'fail',
            'message' => 'You cannot accept the invitation now. Please try again later.'
        ], 503);
    }
}
