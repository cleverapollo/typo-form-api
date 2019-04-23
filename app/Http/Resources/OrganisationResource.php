<?php

namespace App\Http\Resources;

use Auth;
use App\Models\Role;
use App\Models\Type;
use App\Models\Invitation;
use Illuminate\Http\Resources\Json\JsonResource;

class OrganisationResource extends JsonResource
{
	/**
	 * Transform the resource into an array.
	 *
	 * @param  \Illuminate\Http\Request
	 * @return array
	 */
	public function toArray($request)
	{
        $type = Type::where('name', 'organisation')->first();
		return [
			'id' => $this->id,
			'name' => $this->name,
			'application_id' => $this->application_id,
			'organisation_role_id' => $this->whenPivotLoaded('organisation_users', function () {
				return $this->pivot->role_id;
			}),
            'forms_length' => count($this->forms),
            'active_users_length' => count($this->users),
            'invited_users_length' => count(Invitation::where([
                'reference_id' => $this->id,
                'status' => 0,
                'type_id' => $type->id
            ])->get()),
            'created_at' => $this->created_at
		];
	}
}