<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Application extends Model
{
	/**
	 * Delete children
	 */
	protected static function boot()
	{
		parent::boot();

		static::deleting(function ($application) {
			$application->forms->each(function ($form) {
				$form->delete();
			});
		});
	}

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'name', 'slug', 'css', 'icon', 'share_token', 'logo', 'primary_color', 'secondary_color', 'background_image'
	];

	/**
	 * Get the users for the Application.
	 */
	public function users()
	{
		return $this->belongsToMany('App\User', 'application_users')->withPivot('role_id')->withTimestamps();
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
	 * Get the application emails for the Application.
	 */
	public function emails()
	{
		return $this->hasMany('App\Models\ApplicationEmail');
	}

	/**
	 * Get all of the Application's meta data
	 */
	public function metas()
	{
		return $this->morphMany('App\Models\Meta', 'metable');
	}
}
