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
            if ($invitations && count($invitations) > 0) {
                foreach ($invitations as $invitation) {
                    $token =
                    $this->invite(
                        $application->name,
                        $user->first_name . " " . $user->last_name,
                        $invitation->email,
                        $invitation->role
                    );

                    // Input to the application_invitations table
                    DB::table('application_invitations')->insert([
                        'inviter_id' => $user->id,
                        'invitee' => $invitation->email,
                        'application_id' => $application->id,
                        'role' => $invitation->role
                    ]);
                }
            }

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
     * Send email
     *
     * @param $applicationName
     * @param $userName
     * @param $email
     * @param $role
     */
    protected function invite($applicationName, $userName, $email, $role)
    {
        Mail::send('emails.invitationToApplication', [
            'applicationName' => $applicationName,
            'userName' => $userName,
            'role' => $role
        ], function ($message) use ($email) {
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
