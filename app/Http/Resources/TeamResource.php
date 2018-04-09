<?php

namespace App\Http\Resources;

use App\Models\Role;
use Illuminate\Http\Resources\Json\JsonResource;

class TeamResource extends JsonResource
{
	/**
	 * Transform the resource into an array.
	 *
	 * @param  \Illuminate\Http\Request
	 * @return array
	 */
	public function toArray($request)
	{
		$team_role_id = $this->whenPivotLoaded('team_users', function () {
			return $this->pivot->role_id;
		});

		return [
			'id' => $this->id,
			'name' => $this->name,
			'description' => $this->description,
			'application_id' => $this->application_id,
			'team_role_id' => $team_role_id,
			'share_token' => Role::find($team_role_id)->name == 'Admin' ? $this->share_token : null
		];
	}
}