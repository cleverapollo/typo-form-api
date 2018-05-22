<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Action extends Model
{
	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'user_id', 'action_id', 'action_type_id', 'trigger_at'
	];

	/**
	 * Get the action type for the Action.
	 */
	public function type()
	{
		return $this->belongsTo('App\Models\ActionType');
	}
}
