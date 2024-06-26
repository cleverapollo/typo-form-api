<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class FormTemplateResource extends JsonResource
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
            'type_id' => $this->type_id,
			'name' => $this->name,
			'application_id' => $this->application_id,
			'show_progress' => $this->show_progress,
			'allow_submit' => $this->allow_submit,
			'auto' => $this->auto,
            'metas' => $this->metas,
            'forms_length' => count($this->forms),
            'created_at' => $this->created_at,
            'status_id' => $this->status_id
		];
	}
}