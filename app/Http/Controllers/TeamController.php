<?php

namespace App\Http\Controllers;

use Auth;
use App\Models\Team;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

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
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $teams = Auth::user()->team()->get();
        return response()->json([
            'status' => 'success',
            'teams' => $teams
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
        $team = $user->team()->Create($request->only(['name', 'description']));
        if ($team) {
            // Update user role in user_teams table
            DB::table('user_teams')->where([
                ['user_id', '=', $user->id],
                ['team_id', '=', $team->id],
            ])->update(['role' => 'Owner']);

            // Send email to other users
            $emails = $request->input('emails', []);

            if ($emails && count($emails) > 0) {
                foreach ($emails as $email) {
                    $this->invite($team->name, $user->first_name . " " . $user->last_name, $email);

                    // Input to the team_invitations table
                    DB::table('team_invitations')->insert([
                        'inviter_id' => $user->id,
                        'invitee' => $email,
                        'team_id' => $team->id
                    ]);
                }
            }

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
     * Send email
     *
     * @param $teamName
     * @param $userName
     * @param $email
     */
    protected function invite($teamName, $userName, $email)
    {
        Mail::send('emails.invitationToTeam', ['teamName' => $teamName, 'userName' => $userName], function ($message) use ($email) {
            $message->from('info@informed365.com', 'Informed 365');
            $message->to($email);
        });
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $team = Team::where('id', $id)->get();
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
     * @param  \Illuminate\Http\Request $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'name' => 'filled'
        ]);

        $team = Team::find($id);
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
        if (Team::destroy($id)) {
            return response()->json(['status' => 'success'], 200);
        }
        return response()->json([
            'status' => 'fail',
            'message' => $this->generateErrorMessage('team', 503, 'delete')
        ], 503);
    }
}
