<?php

namespace App\Http\Controllers;

use Auth;
use Illuminate\Http\Request;
use App\Jobs\ApplicationInvitationJob;
use App\Models\Application;
use App\Models\Type;

class ApplicationInvitationController extends Controller
{
    /**
     * Constructor
     */
    public function __construct() {
		$this->middleware('auth:api');
	}

    /**
     * Store Application Invitation
     *
     * @param string $application_slug
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     *
     * @throws \Exception
     */
    public function store($application_slug, Request $request) {
		$this->validate($request, [
			'invitations' => 'required|array',
			'invitations.*.email' => 'required|email',
            'role_id' => 'required|integer|min:2'
        ]);

        $application = Application::where('slug', $application_slug)->first();
        if (!$application) {
            return $this->returnError('application', 404, 'invite');
        }

        $type = Type::where('name', 'application')->first();
        if (!$type) {
            return $this->returnError('application', 404, 'invite');
        }

        $invitations = $request->input('invitations');
        foreach($invitations as $invitation) {
            $data = [];
            $data['user_id'] = Auth::user()->id;
            $data['invitation'] = $invitation;
            $data['role_id'] = $request->input('role_id');
            $data['type_id'] = $type->id;
            $data['application_id'] = $application->id;
            $data['meta'] = [
                'form_templates' => $request->input('form_templates'),
                'subject' => $request->input('subject'),
                'message' => $request->input('message'),
                'cc' => $request->input('cc'),
                'bcc' => $request->input('bcc'),
                'organisation' => $invitation['organisation'],
            ];
            \Log::info("INVITE-URL-ISSUE--ApplicationInvitationController@handle:: Data: " . json_encode($data));
            dispatch(new ApplicationInvitationJob($data));
        }
        
        return response()->json(['invitations' => 'Application invitations have been queued for sending.']);
    }
}