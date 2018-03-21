<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Meta extends Model
{
	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'metadata', 'metable_id', 'metable_type'
	];

	/**
	 * Get all of the owning metable models
	 */
	public function metable()
	{
		return $this->morphTo();
	}
}
