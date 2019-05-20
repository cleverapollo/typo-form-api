<?php

namespace App\Http\Resources;

use \RoleRepository;
use \UserStatusRepository;
use Illuminate\Http\Resources\Json\JsonResource;

class ApplicationUserResource extends JsonResource
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
            'status' => UserStatusRepository::dictionary($this->status),
            'application_id' => $this->application_id,
            'application_role_id' => $this->role_id,
            'application_role' => RoleRepository::dictionary($this->role_id),
            'id' => $this->user->id,
            'first_name' => $this->user->first_name,
            'last_name' => $this->user->last_name,
            'email' => $this->user->email,
            'role_id' => $this->user->role_id,
            'role' => RoleRepository::dictionary($this->user->role_id),
            'created_at' => $this->user->created_at,
            'updated_at' => $this->user->updated_at,
            'meta' => $this->meta,
        ];
    }
}