<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuestionTrigger extends Model
{
	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'question_id', 'parent_question_id', 'parent_answer_id', 'value', 'comparator_id', 'order', 'operator'
	];

	/**
	 * Get the question that owns the Trigger.
	 */
	public function question()
	{
		return $this->belongsTo('App\Models\Question');
	}

	/**
	 * Get the parent question for the Trigger.
	 */
	public function parentQuestion()
	{
		return $this->belongsTo('App\Models\Question');
	}

	/**
	 * Get the parent answer for the Trigger.
	 */
	public function parentAnswer()
	{
		return $this->belongsTo('App\Models\Answer');
	}

	/**
	 * Get the comparator that owns the Trigger.
	 */
	public function comparator()
	{
		return $this->belongsTo('App\Models\Comparator');
	}

	/**
	 * Get all of the Trigger's meta data
	 */
	public function meta()
	{
		return $this->morphMany('App\Models\Meta', 'metable');
	}
}
