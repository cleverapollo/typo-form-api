<?php

namespace App\Services;

use App\Models\Application;
use App\Models\ApplicationUser;
use App\User;
use Illuminate\Auth\Access\AuthorizationException;

class AclService {
    const SHOW = 'show';   // Can view the resource
    const STORE = 'store'; // Can perform store operations on the resource

    /**
     * Set permissions for $user to perform $ability on $model where id is in $ids
     *
     * @param App\User $user
     * @param Illuminate\Database\Eloquent\Model $model
     * @param string $ability
     * @param Array<int> $ids
     * @return void
     */
    protected function allow(User $user, $model, $ids, $ability)
    {
        $model::whereIn('id', $ids)
            ->get()
            ->map(function($resource) use ($user, $ability) {
                $user->allow($ability, $resource);
            });
    }

    /**
     * Allow passed user to perform the given ability on the given set of resource ids
     *
     * @param User $user
     * @param string Plural version of model representing recourse to be given access to
     * @param Array $ids An array of resources ids
     * @param SHOW|STORE Access level constant
     * @return boolean
     */
    public function allowAccessToResource(User $user, $resource, $ids, $ability)
    {
        $this->allow($user, model_class($resource), $ids, $ability);
    }

    public function authorize(User $user, Application $application, $ability, $resource)
    {
        // If the user is an admin / super admin we can exit early
        if($this->isPermissible($user, $application)) {
            return;
        }

        // If this resources access level is not private, then its acceptable for this user to 
        // access it
        if($resource->accessLevel !== 'private') {
            return;
        }

        // Check the specific ACL level permissions for this user
        if ($user->can($ability, $resource)) {
            return;
        }

        throw new AuthorizationException('Forbidden');
    }

    // The following four functions will act as a replacement for littering 'Super Admin' checks 
    // everywhere. Later it will extend into a proper role check once we start assigning 
    // users to roles. The functionality within is largely a replica of the existing
    // snippets used
    //
    // Replacing existing string checks with this function allows us to perform a cut over later
    // without disrupting any flows
    // 
    public function isSuperAdmin(User $user)
    {
        return ($user->role->name ?? '') === 'Super Admin';
    }

    public function isAdmin(User $user, Application $application)
    {
        $applicationUser = ApplicationUser::where([
            'user_id' => $user->id,
            'application_id' => $application->id
        ])->first();

        return data_get($applicationUser, 'role.name', null) === 'Admin';
    }

    public function isPermissible(User $user, Application $application)
    {
        return $this->isSuperAdmin($user) || $this->isAdmin($user, $application);
    }

    public function adminOrfail(User $user, Application $application)
    {
        if(!$this->isPermissible($user, $application)) {
            throw new AuthorizationException('Forbidden');
        }
    }
}