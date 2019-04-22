<?php

namespace App\Services;

use App\Models\FormTemplate;
use App\User;

class AclService {
    const SHOW = 'show';
    const STORE = 'store';

    /**
     * Set permissions for $user to perform $ability on $model where id is in $ids
     *
     * @param App\User $user
     * @param Illuminate\Database\Eloquent\Model $model
     * @param string $ability
     * @param Array<int> $ids
     * @return void
     */
    protected function allow(User $user, $model, $ability, $ids)
    {
        $model::whereIn('id', $ids)
            ->get()
            ->map(function($resource) use ($user, $ability) {
                $user->allow($ability, $resource);
            });
    }

    /**
     * This function will act as a replacement for littering 'Super Admin' checks everywhere. Later
     * it will extend into a proper role check once we start assigning users to roles.
     * 
     * Replacing existing string checks with this function allows us to perform a cut over later
     * without disrupting any flows
     * 
     * TODO Create role "super admin" in acl_roles table
     *
     * @param User $user
     * @return boolean
     */
    public function isSuperAdmin(User $user)
    {
        return $user->role->name == 'Super Admin';
    }

    /**
     * Allow passed user to show the provided form template ids
     *
     * @param User $user
     * @param Array $formTemplateIds An array of form template ids
     * @return boolean
     */
    public function canShowFormTemplate(User $user, $formTemplateIds)
    {
        $this->allow($user, FormTemplate::class, AclService::SHOW, $formTemplateIds);
    }

    /**
     * Allow passed user to store the provided form template ids
     *
     * @param User $user
     * @param Array $formTemplateIds An array of form template ids
     * @return boolean
     */
    public function canStoreFormTemplate(User $user, $formTemplateIds)
    {
        $this->allow($user, FormTemplate::class, AclService::STORE, $formTemplateIds);
    }
}