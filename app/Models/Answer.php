<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Answer extends Model
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
		'answer', 'question_id', 'order'
	];

	/**
	 * Get the question that owns the Answer.
	 */
	public function question()
	{
		return $this->belongsTo('App\Models\Question');
	}

	/**
	 * Get all of the Answer's meta data
	 */
	public function meta()
	{
		return $this->morphMany('App\Models\Meta', 'metable');
	}
}
