<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActionType extends Model
{
	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'type'
	];

	/**
	 * Get the actions for the Action Type.
	 */
	public function actions()
	{
		return $this->hasMany('App\Models\Action');
	}
}
