<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class FormResource extends JsonResource
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
			'application_id' => $this->application_id,
			'period_start' => $this->period_start,
			'period_end' => $this->period_end,
			'period_id' => $this->period_id,
			'show_progress' => $this->show_progress,
			'auto' => $this->auto
		];
	}
}