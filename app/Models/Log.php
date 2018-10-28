<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Log extends Model
{
	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'user_id', 'application_id', 'action', 'resourcable_id', 'resourcable_type', 'ip_address'
	];

	/**
	 * Get all of the owning logable models
	 */
	public function resourceable()
	{
		return $this->morphTo();
	}
}
