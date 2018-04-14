<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Status extends Model
{
	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'status'
	];

	/**
	 * Get the submissions for the Status.
	 */
	public function submissions()
	{
		return $this->hasMany('App\Models\Submission');
	}
}
