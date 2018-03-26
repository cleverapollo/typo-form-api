<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TeamInvitation extends Model
{
	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'inviter_id', 'invitee', 'team_id', 'role_id', 'token', 'status'
	];

	/**
	 * Get the inviter that sent the Invitation.
	 */
	public function inviter()
	{
		return $this->belongsTo('App\User');
	}

	/**
	 * Get the team that owns the Invitation.
	 */
	public function team()
	{
		return $this->belongsTo('App\Models\Team');
	}
}
