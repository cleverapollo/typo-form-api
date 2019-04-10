<?php

namespace App\Http\Resources;

use App\Models\Type;
use Illuminate\Http\Resources\Json\JsonResource;

class InvitationResource extends JsonResource
{
	/**
	 * Transform the resource into an array.
	 *
	 * @param  \Illuminate\Http\Request
	 * @return array
	 */
	public function toArray($request)
	{
        $type = Type::find($this->type_id);
		return [
			'id' => $this->id,
			'inviter_id' => $this->inviter_id,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
			'email' => $this->email,
            'meta' => $this->meta,
			$type->name . '_id' => $this->reference_id,
            $type->name . '_role_id' => $this->role_id,
			'created_at' => $this->created_at
		];
	}
}