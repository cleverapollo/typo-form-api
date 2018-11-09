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
			'user' => [
				'id' => $this->user->id,
				'first_name' => $this->user->first_name,
				'last_name' => $this->user->last_name,
				'email' => $this->user->email
			],
			'organisation' => $this->organisation ? [
				'id' => $this->organisation->id,
				'name' => $this->organisation->name,
				'description' => $this->organisation->description
			] : null,
			'form' => [
				'id' => $this->form_id,
				'name' => $this->form->name
			],
			'progress' => $this->progress,
			'period_start' => $this->period_start,
			'period_end' => $this->period_end,
			'status_id' => $this->status_id,
			'created_at' => $this->created_at,
			'updated_at' => $this->updated_at,
			'responses' => ResponseResource::collection($this->responses)
		];
	}
}