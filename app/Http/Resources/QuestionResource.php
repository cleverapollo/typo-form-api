<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class QuestionResource extends JsonResource
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
			'question' => $this->question,
			'description' => $this->description,
			'mandatory' => $this->mandatory,
			'question_type_id' => $this->question_type_id,
			'order' => $this->order,
			'width' => $this->width,
			'answers' => AnswerResource::collection($this->answers)
		];
	}
}