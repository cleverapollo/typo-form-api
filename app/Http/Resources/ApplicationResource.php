<?php

namespace App\Http\Resources;

use App\Models\Role;
use Illuminate\Http\Resources\Json\JsonResource;

class ApplicationResource extends JsonResource
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
			'css' => $this->css,
			'application_role_id' => $this->whenPivotLoaded('application_users', function () {
				return $this->pivot->role_id;
			}),
			'share_token' => $this->whenPivotLoaded('application_users', function () {
				return (Role::find($this->pivot->role_id)->name == 'Admin') ? $this->share_token : null;
			})
		];
	}
}