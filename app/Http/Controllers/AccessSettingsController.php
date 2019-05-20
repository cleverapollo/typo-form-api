<?php

namespace App\Http\Controllers;

use App\Http\Resources\AccessSettingsResource;
use App\Models\AccessLevel;
use App\Services\AclFacade as Acl;
use Auth;
use Illuminate\Http\Request;

class AccessSettingsController extends Controller
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
     * Display the specified resource
     *
     * @param Model $resource
     * @param int $id
     */
    public function show($resource, $id)
    {
        $user = Auth::user();
        $resource = $this->getResource($resource, $id);
        Acl::adminOrfail($user, $resource->application);

        $accessSettings = $resource->accessSettings()->firstOrFail();
        return new AccessSettingsResource($accessSettings);
    }

    /**
     * Update the specified resource
     *
     * @param Model $resource
     * @param int $id 
     * @param Request $request
     */
    public function update($resource, $id, Request $request)
    {
        $user = Auth::user();
        $resource = $this->getResource($resource, $id);
        Acl::adminOrfail($user, $resource->application);

        $this->validate($request, [
            'access_level' => 'required|string',
        ]);

        $accessLevel = AccessLevel::whereValue($request->access_level)->firstOrFail()->id;

        $accessSettings = $resource
            ->accessSettings()
            ->updateOrCreate([], [
                'access_level_id' => $accessLevel,
            ]);

        return new AccessSettingsResource($accessSettings);
    }

    /**
     * Get model instance from resource string and id
     *
     * @param string $resource
     * @param int $id
     */
    protected function getResource($resource, $id)
    {
        $modelClass = model_class($resource);
        return $modelClass::findOrFail($id);
    }
}
