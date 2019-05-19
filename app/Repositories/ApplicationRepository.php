<?php

namespace App\Repositories;

use \Acl;
use App\Exceptions\MissingApplicationException;
use App\Models\Application;
use App\Models\ApplicationUser;
use App\User;

class ApplicationRepository {
    /**
     * Fetch the slug matching application for the user
     *
     * @param User $user
     * @param string|null $slug
     * @return Application
     * @throws MissingApplicationException
     */
    public function bySlug(User $user, ?string $slug)
    {
        $application = $this->bySlugLax($user, $slug);

        if (!$application) {
            throw new MissingApplicationException();
        }

        return $application;
    }

    /**
     * Similar to bySlug but won't throw if the application is not found
     *
     * @param User $user
     * @param string|null $slug
     * @return Application|null
     */
    public function bySlugLax(User $user, ?string $slug)
    {
        if (Acl::isSuperAdmin($user)) {
            return Application::whereSlug($slug)->first();
        } else {
            return $user->applications()->whereSlug($slug)->first();
        }
    }
}