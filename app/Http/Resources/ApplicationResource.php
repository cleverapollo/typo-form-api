<?php

namespace App\Http\Resources;

use Auth;
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
			'application_role_id' => $this->whenPivotLoaded('application_users', function () {
				return $this->pivot->role_id;
			}),
			'share_token' => Auth::user()->applications()->find($this->id)->role->name == 'Admin' ? $this->share_token : null
		];
	}
}