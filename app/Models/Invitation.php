<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invitation extends Model
{
	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
        'inviter_id', 'first_name', 'last_name', 'email', 'properties', 'reference_id', 'type_id', 'role_id', 'status'
	];

    protected $casts = [
        'properties' => 'array'
    ];

	/**
	 * Get the inviter that sent the Invitation.
	 */
	public function inviter()
	{
		return $this->belongsTo('App\User');
	}

    /**
     * Get the role that owns the Invitation
     */
    public function role()
    {
        return $this->belongsTo('App\Models\Role');
    }

    /**
     * Get the type that owns the Invitation
     */
    public function type()
    {
        return $this->belongsTo('App\Models\Type');
    }
}
