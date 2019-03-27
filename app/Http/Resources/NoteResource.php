<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class NoteResource extends JsonResource
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
			'event' => $this->event,
            'note' => $this->note,
            'user' => [
                'id' => $this->created_by->id,
                'first_name' => $this->created_by->first_name,
                'last_name' => $this->created_by->last_name,
                'email' => $this->created_by->email
            ],
            'recordable_id' => $this->recordable_id,
            'recordable_type' => $this->recordable_type
		];
	}
}