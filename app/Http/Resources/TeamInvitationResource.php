<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TeamInvitationResource extends JsonResource
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
			'team_id' => $this->team_id,
			'team_role_id' => $this->role_id,
			'created_at' => $this->created_at
		];
	}
}