<?php

namespace App\Repositories;

use \UserStatusRepository;
use App\Models\OrganisationUser;
use App\User;

class OrganisationUserRepository {
    
    /**
     * Add an organisation user if they don't already exist for this organisation
     *
     * @param int $organisationId
     * @param int $userId
     * @param int $roleId
     * @return OrganisationUser
     */
    public function addActiveUser($organisationId, $userId, $roleId)
    {
        // If the user already exists on the organisation we can exit early with the found user
        $organisationUser = $this->find($organisationId, $userId);
        if($organisationUser) {
            return $organisationUser;
        }

        return OrganisationUser::create([
            'user_id' => $userId,
            'organisation_id' => $organisationId,
            'role_id' => $roleId,
            'status' => UserStatusRepository::idByLabel('Active'),
        ]);
    }

    /**
     * Invite user to organisation
     *
     * @param int $organisationId
     * @param int $userId
     * @param int $roleId
     * @param int $inviterId
     * @param Object $meta
     * @return void
     */
    public function inviteUser($organisationId, $userId, $roleId, $inviterId, $meta)
    {
        return OrganisationUser::create([
            'user_id' => $userId,
            'organisation_id' => $organisationId,
            'role_id' => $roleId,
            'status' => UserStatusRepository::idByLabel('Invited'),
            'meta' => [
                'inviter_id' => $inviterId,
                'invite' => $meta,
            ],
        ]);
    }

    public function invitations($applicationId, $userId)
    {
        return OrganisationUser
            ::whereHas('organisation', function($query) use ($applicationId) {
                $query->whereApplicationId($applicationId);
            })
            ->whereUserId($userId)
            ->whereStatus(UserStatusRepository::idByLabel('Invited'))
            ->get();
    }

    /**
     * Is the user in the organisation at all, either invited or active
     *
     * @param int $organisationId
     * @param int $userId
     * @return boolean
     */
    public function isUserInOrganisation($organisationId, $userId)
    {
        return OrganisationUser
            ::whereOrganisationId($organisationId)
            ->whereUserId($userId)
            ->get()
            ->isNotEmpty();
    }

    public function find($applicationId, $userId)
    {
        return OrganisationUser
            ::whereOrganisationId($applicationId)
            ->whereUserId($userId)
            ->first();
    }
}
