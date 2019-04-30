<?php

namespace App\Http\Controllers;

use App\Http\Resources\WorkflowResource;
use App\Models\Workflow;
use App\Repositories\WorkflowRepositoryFacade as WorkflowRepository;
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

    public function index(Request $request)
    {
        // TODO permission checks
        // TODO validation checks
        return WorkflowRepository::all();
    }

    public function show($application_slug, $id)
    {
        // TODO permission checks
        // TODO validation checks
        return WorkflowRepository::byId($id);
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