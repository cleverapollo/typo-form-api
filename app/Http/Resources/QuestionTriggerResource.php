<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class QuestionTriggerResource extends JsonResource
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
			'parent_question_id' => $this->parent_question_id,
			'parent_answer_id' => $this->parent_answer_id,
			'value' => $this->value,
			'comparator_id' => $this->comparator_id,
			'order' => $this->order,
			'operator' => $this->operator
		];
	}
}