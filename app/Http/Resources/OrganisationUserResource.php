<?php

namespace App\Http\Resources;

use \RoleRepository;
use \UserStatusRepository;
use Illuminate\Http\Resources\Json\JsonResource;

class OrganisationUserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'user_id' => $this->user_id,
            'organisation_id' => $this->organisation_id,
            'organisation_role_id' => $this->role_id,
            'organisation_role' => RoleRepository::dictionary($this->role_id),
            'status' => UserStatusRepository::dictionary($this->status),
        ];
    }
}