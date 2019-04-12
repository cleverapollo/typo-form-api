<?php

namespace App\Http\Controllers;

use Auth;
use Illuminate\Http\Request;
use App\Jobs\ApplicationInvitationJob;

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
     * @param String $application_slug
     * @param Request $request
     * @return void
     */
    public function store($application_slug, Request $request) {
		$this->validate($request, [
			'invitations' => 'array',
			'invitations.*.email' => 'required|email',
            'role_id' => 'required|integer|min:2'
        ]);

        $invitations = $request->input('invitations');
        foreach($invitations as $invitation) {
            $data = $request->all();
            $data['user_id'] = Auth::user()->id;
            dispatch(new ApplicationInvitationJob($data));
        }
        
        return response()->json(['invitations' => 'Application invitations have been queued for sending.']);
    }
}