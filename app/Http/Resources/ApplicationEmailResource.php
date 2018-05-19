<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ApplicationEmailResource extends JsonResource
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
			'application_id' => $this->application_id,
			'recipients' => $this->recipients,
			'subject' => $this->subject,
			'body' => $this->body
		];
	}
}