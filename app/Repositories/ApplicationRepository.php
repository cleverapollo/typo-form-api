<?php

namespace App\Repositories;

use App\Exceptions\MissingApplicationException;
use App\Models\Application;
use App\Services\AclFacade as Acl;
use App\User;

class ApplicationRepository {
    /**
     * Fetch the default application for the user
     *
     * @param User $user
     * @param string $slug
     * @return void
     */
    public function bySlug(User $user, string $slug)
    {
        if (Acl::isSuperAdmin($user)) {
            $application = Application::where('slug', $slug)->first();
        } else {
            $application = $user->applications()->where('slug', $slug)->first();
        }

        if (!$application) {
            throw new MissingApplicationException();
        }

        return $application;
    }
}