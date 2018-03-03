<?php

namespace App\Http\Controllers;

use App\Models\Team;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class TeamController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $team = Auth::user()->team()->get();
        return response()->json(['status' => 'success', 'result' => $team], 200);
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
            'name' => 'required|max:255'
        ]);

        $user = Auth::user();
        $team = $user->team()->Create($request->only(['name']));
        if ($team) {
            // Send email to other users
            $emails = json_decode($request->input('emails'));

            if ($emails && count($emails) > 0) {
                foreach ($emails as $email) {
                    $this->invite($team->name, $user->first_name . " " . $user->last_name, $email);
                }
            }

            return response()->json(['status' => 'success', 'result' => $team], 200);
        }
        return response()->json(['status' => 'fail'], 503);
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
        return response()->json($team);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $team = Team::where('id', $id)->get();
        return view('team.editTeam', ['teams' => $team]);
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
        if ($team->fill($request->all())->save()) {
            return response()->json(['status' => 'success', 'result' => $team], 200);
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
        if (Team::destroy($id)) {
            return response()->json(['status' => 'success'], 200);
        }

        return response()->json(['status' => 'fail']);
    }
}
