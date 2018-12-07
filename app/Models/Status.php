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
	 * Get the forms for the Status.
	 */
	public function forms()
	{
		return $this->hasMany('App\Models\Form');
	}
}
