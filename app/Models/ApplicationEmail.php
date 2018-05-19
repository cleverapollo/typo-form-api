<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApplicationEmail extends Model
{
	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'application_id', 'recipients', 'subject', 'body'
	];

	/**
	 * Get the application that owns the Application Email.
	 */
	public function application()
	{
		return $this->belongsTo('App\Models\Application');
	}
}
