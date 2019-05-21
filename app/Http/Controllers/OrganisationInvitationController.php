<?php

namespace App\Http\Controllers;

use Auth;
use Illuminate\Http\Request;
use App\Jobs\OrganisationInvitationJob;
use App\Models\Application;
use App\Models\Type;

class OrganisationInvitationController extends Controller
{
    /**
     * Constructor
     */
    public function __construct() {
		$this->middleware('auth:api');
	}

    /**
     * Store Organisation Invitation
     *
     * @param string $application_slug
     * @param integer $id
     * @param Request $request
     * @return void
     */
    public function store($application_slug, $id, Request $request) {
		$this->validate($request, [
			'invitations' => 'required|array',
			'invitations.*.email' => 'required|email',
            'role_id' => 'required|integer|min:2'
        ]);

        $application = Application::where('slug', $application_slug)->first();
        if (!$application) {
            return $this->returnError('organisation', 404, 'invite');
        }

        // Get Organisation
        if(!$organisation = $application->organisations()->where('application_id', $application->id)->where('id', $id)->first()) {
            return $this->returnError('organisation', 404, 'send invitation');
        }

        $type = Type::where('name', 'organisation')->first();
        if (!$type) {
            return $this->returnError('organisation', 404, 'invite');
        }

        $invitations = $request->input('invitations');
        foreach($invitations as $invitation) {
            $data = [];
            $data['user_id'] = Auth::user()->id;
            $data['invitation'] = $invitation;
            $data['role_id'] = $request->input('role_id');
            $data['type_id'] = $type->id;
            $data['organisation_id'] = $organisation->id;
            $data['application_id'] = $application->id;
            $data['meta'] = [
                'subject' => $request->input('subject'),
                'message' => $request->input('message'),
                'cc' => $request->input('cc'),
                'bcc' => $request->input('bcc')
            ];
    
            dispatch(new OrganisationInvitationJob($data));
        }
        
        return response()->json(['invitations' => 'Application invitations have been queued for sending.']);
    }
}