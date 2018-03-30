<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Response extends Model
{
	use SoftDeletes;

	/**
	 * The attributes that should be mutated to dates.
	 *
	 * @var array
	 */
	protected $dates = ['deleted_at'];

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'response', 'question_id', 'answer_id', 'submission_id'
	];

	/**
	 * Get the question that owns the Response.
	 */
	public function question()
	{
		return $this->belongsTo('App\Models\Question');
	}

	/**
	 * Get the answer that owns the Response.
	 */
	public function answer()
	{
		return $this->belongsTo('App\Models\Answer');
	}

	/**
	 * Get the submission that owns the Response.
	 */
	public function submission()
	{
		return $this->belongsTo('App\Models\Submission');
	}

	/**
	 * Get all of the Responses's meta data
	 */
	public function meta()
	{
		return $this->morphMany('App\Models\Meta', 'metable');
	}
}
