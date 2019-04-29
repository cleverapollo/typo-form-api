<?php

namespace App\Http\Controllers;

use App\Http\Resources\WorkflowResource;
use App\Models\Workflow;
use Auth;
use Illuminate\Http\Request;

class WorkflowController extends Controller
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

    public function store(Request $request)
    {
        // TODO permission checks
        $input = $this->validate($request, [
            'name' => 'required|string',
            'trigger' => 'required|string',
            'trigger_config' => 'required|json',
            'action' => 'required|string',
            'action_config' => 'required|json',
            'delay' => 'required|numeric',
            'active_to' => 'required|date',
            'active_from' => 'required|date',
        ]);

        $input['author_id'] = Auth::user()->id;
        $input['status'] = 1;

        $workflow = Workflow::create($input);
    }
}