<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApplicationUser extends Model
{
	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'user_id', 'application_id', 'role_id'
	];

	/**
	 * Get the role of the User in Application
	 */
	public function role()
	{
		return $this->belongsTo('App\Models\Role');
	}
}
