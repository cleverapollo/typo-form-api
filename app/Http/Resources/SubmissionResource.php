<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SubmissionResource extends JsonResource
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
			'form_id' => $this->form_id,
			'user' => [
				'first_name' => $this->user->first_name,
				'last_name' => $this->user->last_name,
				'email' => $this->user->email
			],
			'team' => $this->team ? [
				'name' => $this->team->name,
				'description' => $this->team->description
			] : null,
			'progress' => $this->progress,
			'period_start' => $this->period_start,
			'period_end' => $this->period_end,
			'responses' => ResponseResource::collection($this->responses)
		];
	}
}