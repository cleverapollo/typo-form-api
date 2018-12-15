<?php

namespace App\Http\Resources;

use Auth;
use App\Models\Role;
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
		return [
			'id' => $this->id,
			'name' => $this->name,
			'description' => $this->description,
			'application_id' => $this->application_id,
			'organisation_role_id' => $this->whenPivotLoaded('organisation_users', function () {
				return $this->pivot->role_id;
			}),
			'share_token' => Auth::user()->role->name == 'Super Admin' ? $this->share_token : $this->whenPivotLoaded('organisation_users', function () {
				return Role::find($this->pivot->role_id)->name == 'Admin' ? $this->share_token : null;
			})
		];
	}
}