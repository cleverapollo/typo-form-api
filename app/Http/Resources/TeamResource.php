<?php

namespace App\Http\Resources;

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
		return [
			'id' => $this->id,
			'name' => $this->name,
			'description' => $this->description,
			'application_id' => $this->application_id,
			'pivot' => $this->whenPivotLoaded('team_users', function () {
				return $this->pivot;
			})
		];
	}
}