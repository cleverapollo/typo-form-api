<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApplicationUser extends Model
{
	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'user_id', 'application_id', 'role_id', 'meta'
	];

    protected $casts = [
        'meta' => 'array'
    ];

	/**
	 * Get the role of the User in Application
	 */
	public function role()
	{
		return $this->belongsTo('App\Models\Role');
	}

    /**
     * Get all of the ApplicationUser's logs.
     */
    public function logs()
    {
        return $this->morphMany('App\Models\Log', 'resourcable');
    }
}
