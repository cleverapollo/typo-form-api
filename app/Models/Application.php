<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Application extends Model
{
	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'name', 'share_token'
	];

	/**
	 * Get the users for the Application.
	 */
	public function users()
	{
		return $this->belongsToMany('App\User', 'application_users')->withPivot('role_id');
	}

	/**
	 * Get the forms for the Application.
	 */
	public function forms()
	{
		return $this->hasMany('App\Models\Form');
	}

	/**
	 * Get the teams for the Application.
	 */
	public function teams()
	{
		return $this->hasMany('App\Models\Team');
	}

	/**
	 * Get all of the Application's meta data
	 */
	public function meta()
	{
		return $this->morphMany('App\Models\Meta', 'metable');
	}
}
