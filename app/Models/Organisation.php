<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Organisation extends Model
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
	 * Get the users that belongs to the Organisation.
	 */
	public function users()
	{
		return $this->belongsToMany('App\User', 'organisation_users')->withPivot('role_id')->withTimestamps();
	}

	/**
	 * Get the forms for the Organisation.
	 */
	public function forms()
	{
		return $this->hasMany('App\Models\Form');
	}

	/**
	 * Get the application that owns the Organisation.
	 */
	public function application()
	{
		return $this->belongsTo('App\Models\Application');
	}

	/**
	 * Get all of the Organisation's meta data
	 */
	public function metas()
	{
		return $this->morphMany('App\Models\Meta', 'metable');
	}

    /**
     * Get all of the Organisation's logs.
     */
    public function logs()
    {
        return $this->morphMany('App\Models\Log', 'resourcable');
    }
}
