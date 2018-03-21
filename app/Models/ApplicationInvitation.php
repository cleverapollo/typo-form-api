<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApplicationInvitation extends Model
{
	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'inviter_id', 'invitee', 'application_id', 'role', 'token', 'status'
	];

	/**
	 * Get the inviter that sent the Invitation.
	 */
	public function inviter()
	{
		return $this->belongsTo('App\User');
	}

	/**
	 * Get the application that owns the Invitation.
	 */
	public function application()
	{
		return $this->belongsTo('App\Models\Application');
	}
}
