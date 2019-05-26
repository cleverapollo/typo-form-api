<?php

namespace App\Http\Resources;

use \RoleRepository;
use Illuminate\Http\Resources\Json\JsonResource;

class AuthResource extends JsonResource
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
			'role_id' => $this->role_id,
			'role' => RoleRepository::dictionary($this->role_id),
			'api_token' => $this->api_token,
			'created_at' => $this->created_at,
			'updated_at' => $this->updated_at
		];
	}
}