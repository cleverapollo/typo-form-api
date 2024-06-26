<?php

namespace App\Http\Resources;

use Auth;
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
			'slug' => $this->slug,
			'css' => $this->css,
            'support_text' => $this->support_text,
            'join_flag' => $this->join_flag,
			'icon' => $this->icon,
			'logo' => $this->logo,
			'background_image' => $this->background_image,
			'application_role_id' => $this->whenPivotLoaded('application_users', function () {
				return $this->pivot->role_id;
			}),
			'share_token' => Auth::user() && Auth::user()->role->name == 'Super Admin' ? $this->share_token : $this->whenPivotLoaded('application_users', function () {
				return Role::find($this->pivot->role_id)->name == 'Admin' ? $this->share_token : null;
			}),
            'default_route' => $this->default_route,
            'metas' => $this->metas
		];
	}
}