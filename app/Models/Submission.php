<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Submission extends Model
{
	use SoftDeletes;

	/**
	 * The attributes that should be mutated to dates.
	 *
	 * @var array
	 */
	protected $dates = ['deleted_at'];

	/**
	 * Delete children
	 */
	protected static function boot()
	{
		parent::boot();

		static::deleting(function ($submission) {
			$submission->responses->each(function ($response) {
				$response->delete();
			});
		});
	}

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'form_id', 'user_id', 'team_id', 'progress', 'period_start', 'period_end'
	];

	/**
	 * Get the form that owns the Submission.
	 */
	public function form()
	{
		return $this->belongsTo('App\Models\Form');
	}

	/**
	 * Get the user that owns the Submission.
	 */
	public function user()
	{
		return $this->belongsTo('App\User');
	}

	/**
	 * Get the team that owns the Submission.
	 */
	public function team()
	{
		return $this->belongsTo('App\Models\Team');
	}

	/**
	 * Get the responses for the Submission.
	 */
	public function responses()
	{
		return $this->hasMany('App\Models\Response');
	}

	/**
	 * Get all of the Submission's meta data
	 */
	public function meta()
	{
		return $this->morphMany('App\Models\Meta', 'metable');
	}
}
