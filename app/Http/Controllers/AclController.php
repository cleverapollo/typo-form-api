<?php

namespace App\Http\Controllers;

use App\Http\Resources\AclResource;
use App\Http\Resources\AclResourceResource;
use App\Services\AclFacade as Acl;
use App\Services\AclService;
use App\User;
use Auth;
use Illuminate\Http\Request;
use Silber\Bouncer\Database\Ability;

class AclController extends Controller
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
     * Return the current users permission list 
     *
     * @return AclResource
     */
    public function index()
    {
        $user = Auth::user();
        return AclResource::collection($user->getAbilities());
    }

    /**
     * Display the specified resource
     *
     * @param Model $resource
     * @param int $id
     * @return AclResourceResource
     */
    public function show($resource, $id)
    {
        $resource = $this->getResource($resource, $id);
        $abilities = Ability::whereEntityType($resource->getMorphClass())
            ->whereEntityId($id)
            ->get();

        return AclResourceResource::collection($abilities);
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
            'users' => 'present|array',
        ]);

        Ability::whereEntityType($resource->getMorphClass())
            ->whereName(AclService::SHOW)
            ->whereEntityId($id)
            ->delete();

        User::whereIn('id', $request->users)
            ->get()
            ->map(function($user) use ($resource) {
                $user->allow(AclService::SHOW, $resource);
            });

        $abilities = Ability::whereEntityType($resource->getMorphClass())
            ->whereEntityId($id)
            ->get();

        return AclResourceResource::collection($abilities);
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
