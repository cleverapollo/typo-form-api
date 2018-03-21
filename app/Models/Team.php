<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Team extends Model
{
	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'name', 'description', 'application_id', 'share_token'
	];

	/**
	 * Get the users that belongs to the Team.
	 */
	public function users()
	{
		return $this->belongsToMany('App\User', 'team_users')->withPivot('role');
	}

	/**
	 * Get the submissions for the Team.
	 */
	public function submissions()
	{
		return $this->hasMany('App\Models\Submission');
	}

	/**
	 * Get the application that owns the Team.
	 */
	public function application()
	{
		return $this->belongsTo('App\Models\Application');
	}

	/**
	 * Get the invitations for the Team.
	 */
	public function invitations()
	{
		return $this->hasMany('App\Models\TeamInvitation');
	}

	/**
	 * Get all of the Team's meta data
	 */
	public function meta()
	{
		return $this->morphMany('App\Models\Meta', 'metable');
	}
}
