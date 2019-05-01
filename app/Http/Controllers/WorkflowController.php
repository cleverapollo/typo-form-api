<?php

namespace App\Http\Controllers;

use Acl;
use App\Http\Resources\WorkflowResource;
use App\Models\Workflow;
use App\Repositories\ApplicationRepositoryFacade as ApplicationRepository;
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

    public function index($application_slug)
    {
        $user = Auth::user();
        $application = ApplicationRepository::bySlug($user, $application_slug);
        Acl::adminOrfail($user, $application);

        $workflows = WorkflowRepository::all($user, $application);
        return WorkflowResource::collection($workflows);
    }

    public function show($application_slug, $id)
    {
        $user = Auth::user();
        $application = ApplicationRepository::bySlug($user, $application_slug);
        Acl::adminOrfail($user, $application);

        $workflow = WorkflowRepository::byId($user, $application, $id);
        return new WorkflowResource($workflow);
    }

    public function store(Request $request, $application_slug)
    {
        $user = Auth::user();
        $application = ApplicationRepository::bySlug($user, $application_slug);
        Acl::adminOrfail($user, $application);

        $input = $this->validate($request, [
            'name' => 'required|string',
            'config' => 'required|json',
            'trigger' => 'required|string',
            'trigger_config' => 'required|json',
            'action' => 'required|string',
            'action_config' => 'required|json',
            'delay' => 'required|numeric',
            'active_to' => 'date',
            'active_from' => 'required|date',
        ]);

        $input['author_id'] = $user->id;
        $input['application_id'] = $application->id;
        $input['status'] = WorkflowRepository::getFacadeRoot()::WORKFLOW_STATUS_ACTIVE;

        $workflow = Workflow::create($input);
        return new WorkflowResource($workflow);
    }
}