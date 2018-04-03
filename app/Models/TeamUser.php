<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TeamUser extends Model
{
	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'user_id', 'team_id', 'role_id'
	];

	/**
	 * Get the role of the User in Team
	 */
	public function role()
	{
		return $this->belongsTo('App\Models\Role');
	}
}
