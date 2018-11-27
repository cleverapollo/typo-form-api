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
            'user' => $this->user ? [
                'id' => $this->user->id,
                'first_name' => $this->user->first_name,
                'last_name' => $this->user->last_name,
                'email' => $this->user->email
            ] : null,
            'organisation' => $this->organisation ? [
                'id' => $this->organisation->id,
                'name' => $this->organisation->name,
                'description' => $this->organisation->description
            ] : null,
			'name' => $this->name,
			'application_id' => $this->application_id,
			'show_progress' => $this->show_progress,
			'allow_submit' => $this->allow_submit,
			'auto' => $this->auto,
            'metas' => $this->metas
		];
	}
}