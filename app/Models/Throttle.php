<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Throttle extends Model
{
	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'user_id', 'email', 'ip_address'
	];
}
