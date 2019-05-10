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
			'note_type_id' => $this->note_type_id,
			'description' => $this->description,
            'note' => $this->note,
            'user' => [
                'id' => $this->user->id,
                'first_name' => $this->user->first_name,
                'last_name' => $this->user->last_name,
                'email' => $this->user->email
            ],
            'recordable_id' => $this->recordable_id,
			'recordable_type' => $this->recordable_type,
			'updated_at' => $this->updated_at,
			'task' => $this->task,
			'task_due_at' => $this->task_due_at,
			'completed' => $this->completed
		];
	}
}