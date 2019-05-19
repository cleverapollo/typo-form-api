<?php

namespace App\Http\Resources;

use App\Models\Type;
use Illuminate\Http\Resources\Json\JsonResource;

class InvitationResource extends JsonResource
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
			'inviter_id' => $this->meta['inviter_id'],
			'first_name' => $this->user->first_name,
			'last_name' => $this->user->last_name,
			'email' => $this->user->email,
			'meta' => $this->meta,
			'organisation_id' => $this->organisation_id,
			'organisation_role_id' => $this->role_id,
			'created_at' => $this->created_at
		];
	}
}