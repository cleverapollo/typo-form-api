<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrganisationUser extends Model
{
	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'user_id', 'organisation_id', 'role_id'
	];

	/**
	 * Get the role of the User in Organisation
	 */
	public function role()
	{
		return $this->belongsTo('App\Models\Role');
	}

    /**
     * Get all of the OrganisationUser's logs.
     */
    public function logs()
    {
        return $this->morphMany('App\Models\Log', 'resourcable');
    }
}
