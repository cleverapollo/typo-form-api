<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ValidationResource extends JsonResource
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
			'question_id' => $this->question_id,
			'validation_type_id' => $this->validation_type_id,
			'validation_data' => $this->validation_data
		];
	}
}