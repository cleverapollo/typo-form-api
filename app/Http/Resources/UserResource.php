<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
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
            'id' => $this->id,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'email' => $this->email,
            'role' => $this->role,
            'team_pivot' => $this->whenPivotLoaded('team_users', function () {
	            return $this->pivot;
            }),
            'application_pivot' => $this->whenPivotLoaded('application_users', function () {
	            return $this->pivot;
            })
        ];
    }
}