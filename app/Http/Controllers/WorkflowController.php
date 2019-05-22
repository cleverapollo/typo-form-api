<?php

namespace App\Http\Controllers;

use \Acl;
use \ApplicationRepository;
use \WorkflowRepository;
use App\Http\Resources\WorkflowResource;
use App\Models\Workflow;
use Auth;
use Illuminate\Http\Request;

class WorkflowController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
        $this->middleware('resolve-application-slug');
        $this->middleware('application-admin');

        $this->storeRules = [
            'name' => 'required|string',
            'config' => 'required|json',
            'trigger' => 'required|string',
            'trigger_config' => 'required|json',
            'action' => 'required|string',
            'action_config' => 'required|json',
            'delay' => 'required|numeric',
            'active_to' => 'nullable|date',
            'active_from' => 'required|date',
        ];
        $this->updateRules = [
            'name' => 'filled|string',
            'config' => 'filled|json',
            'trigger' => 'filled|string',
            'trigger_config' => 'filled|json',
            'action' => 'filled|string',
            'action_config' => 'filled|json',
            'delay' => 'filled|numeric',
            'active_to' => 'nullable|date',
            'active_from' => 'filled|date',
        ];
    }

    public function index(Request $request, $application_slug)
    {
        $user = Auth::user();
        $application = $request->get('application');

        $workflows = WorkflowRepository::all($user, $application);
        return WorkflowResource::collection($workflows);
    }

    public function show($application_slug, $id)
    {
        $user = Auth::user();
        $application = $request->get('application');

        $workflow = WorkflowRepository::byId($user, $application, $id);
        return new WorkflowResource($workflow);
    }

    public function destroy($application_slug, $id)
    {
        $user = Auth::user();
        $application = $request->get('application');

        $workflow = WorkflowRepository::byId($user, $application, $id);
        $workflow->delete();
        return [];
    }
    
    protected function prepareRequest($request, $rules) 
    {
        $input = $request->all();

        $input['active_to'] = empty($input['active_to']) ? null : $input['active_to'];
        $request->replace($input);

        $input = $this->validate($request, $rules);

        $input['author_id'] = Auth::user()->id;

        return $input;
    }

    public function store(Request $request)
    {
        $input = $this->prepareRequest($request, $this->storeRules);
        $input['application_id'] = $request->get('application')->id;
        $input['status'] = WorkflowRepository::getFacadeRoot()::WORKFLOW_STATUS_ACTIVE;

        $workflow = Workflow::create($input);

        return new WorkflowResource($workflow);
    }

    public function update(Request $request, $id)
    {
        $input = $this->prepareRequest($request, $this->updateRules);

        $workflow = Workflow::findOrFail($id);
        $workflow->update($input);

        // Any unran jobs are deleted. They will be recreated on the next schedule cycle using the 
        // updated workflow info. Keep non-active jobs around for logging purposes and to make 
        // sure they are not recreated
        WorkflowRepository::deleteActiveJobsOfWorkflow($id);

        return new WorkflowResource($workflow);
    }
}