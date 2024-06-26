<?php

namespace App\Models;

use App\User;
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
			$application->form_templates->each(function ($form_template) {
				$form_template->delete();
			});
		});
	}

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'name', 'slug', 'css', 'icon', 'share_token', 'logo', 'background_image', 'support_text', 'join_flag', 'default_route'
	];

	/**
	 * Get the users for the Application.
	 */
	public function users()
	{
		return $this->belongsToMany(User::class, 'application_users')
			->using(ApplicationUser::class)
			->withPivot('role_id')
			->withTimestamps();
	}

	/**
	 * Get the form_templates for the Application.
	 */
	public function form_templates()
	{
		return $this->hasMany('App\Models\FormTemplate');
	}

	/**
	 * Get the Organisations for the Application.
	 */
	public function organisations()
	{
		return $this->hasMany('App\Models\Organisation');
	}

	/**
	 * Get the application emails for the Application.
	 */
	public function emails()
	{
		return $this->hasMany('App\Models\ApplicationEmail');
	}

    /**
     * Get the form_templates for the Application.
     */
    public function notes()
    {
        return $this->hasMany('App\Models\Note');
    }

	/**
	 * Get all of the Application's meta data
	 */
	public function metas()
	{
		return $this->morphMany('App\Models\Meta', 'metable');
	}

    /**
     * Get the logs for the Application.
     */
    public function logs()
    {
        return $this->hasMany('App\Models\Log');
    }
}
