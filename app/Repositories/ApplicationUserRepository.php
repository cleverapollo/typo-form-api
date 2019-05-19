<?php

namespace App\Repositories;

use \RoleRepository;
use \UserStatusRepository;
use App\Models\ApplicationUser;

class ApplicationUserRepository {

    /**
     * Add an application user if they don't already exist for this application
     *
     * @param int $applicationId
     * @param int $userId
     * @param int $roleId
     * @return ApplicationUser
     */
    public function addActiveUser($applicationId, $userId, $roleId): ApplicationUser
    {
        // If the user already exists on the application we can exit early with the found user
        $applicationUser = $this->find($applicationId, $userId);
        if($applicationUser) {
            return $applicationUser;
        }

        return ApplicationUser::create([
            'user_id' => $userId,
            'application_id' => $applicationId,
            'role_id' => $roleId,
            'status' => UserStatusRepository::idByLabel('Active'),
        ]);
    }

    /**
     * Invite user to application
     *
     * @param int $applicationId
     * @param int $userId
     * @param int $roleId
     * @param int $inviterId
     * @param Object $meta
     * @return void
     */
    public function inviteUser($applicationId, $userId, $roleId, $inviterId, $meta)
    {
        return ApplicationUser::create([
            'user_id' => $userId,
            'application_id' => $applicationId,
            'role_id' => $roleId,
            'status' => UserStatusRepository::idByLabel('Invited'),
            'meta' => [
                'inviter_id' => $inviterId,
                'invite' => $meta,
            ],
        ]);
    }

    public function find($applicationId, $userId)
    {
        return ApplicationUser
            ::whereApplicationId($applicationId)
            ->whereUserId($userId)
            ->first();
    }

    public function findOrFail($applicationId, $userId)
    {
        return ApplicationUser
            ::whereApplicationId($applicationId)
            ->whereUserId($userId)
            ->firstOrFail();
    }

    public function isUserInApplication($applicationId, $userId)
    {
        return ApplicationUser
            ::whereApplicationId($applicationId)
            ->whereUserId($userId)
            ->get()
            ->isNotEmpty();
    }

    public function users($applicationId)
    {
        return ApplicationUser::whereApplicationId($applicationId)->get();
    }

    public function invitations($applicationId, $userId)
    {
        return ApplicationUser
            ::whereApplicationId($applicationId)
            ->whereUserId($userId)
            ->whereStatus(UserStatusRepository::idByLabel('Invited'))
            ->get();
    }
}
