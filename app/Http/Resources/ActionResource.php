<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ActionResource extends JsonResource
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
			'user_id' => $this->user_id,
			'action_id' => $this->action_id,
			'action_type_id' => $this->action_type_id,
			'trigger_at' => $this->trigger_at
		];
	}
}