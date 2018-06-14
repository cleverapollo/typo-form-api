<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ApplicationInvitationResource extends JsonResource
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
			'inviter_id' => $this->inviter_id,
			'invitee' => $this->invitee,
			'application_id' => $this->application_id,
			'application_role_id' => $this->role_id,
			'created_at' => $this->created_at
		];
	}
}