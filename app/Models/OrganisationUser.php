<?php

namespace App\Models;

use App\User;
use Illuminate\Database\Eloquent\Relations\Pivot;

class OrganisationUser extends Pivot
{
    protected $table = 'organisation_users';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'user_id', 'organisation_id', 'role_id', 'meta', 'status'
	];

    protected $casts = [
        'meta' => 'array'
    ];

    /**
     * Get the organisation that owns the OrganisationUser.
     */
    public function organisation()
    {
        return $this->belongsTo('App\Models\Organisation');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

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
