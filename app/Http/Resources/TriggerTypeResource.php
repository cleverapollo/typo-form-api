<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TriggerTypeResource extends JsonResource
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
			'question_type_id' => $this->question_type_id,
			'comparator_id' => $this->comparator_id,
			'answer' => $this->answer,
			'value' => $this->value
		];
	}
}