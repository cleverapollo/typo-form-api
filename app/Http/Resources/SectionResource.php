<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SectionResource extends JsonResource
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
			'order' => $this->order,
			'parent_section_id' => $this->parent_section_id,
			'repeatable' => $this->repeatable,
			'max_rows' => $this->max_rows,
			'min_rows' => $this->min_rows,
			'questions' => QuestionResource::collection($this->questions)
		];
	}
}