<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ValidationType extends Model
{
	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'type'
	];

	/**
	 * Get the validations for the Validation Type.
	 */
	public function validations()
	{
		return $this->hasMany('App\Models\Validation');
	}
}
