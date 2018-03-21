<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'question', 'description', 'mandatory', 'section_id', 'question_type_id', 'order'
	];

	/**
	 * Get the section that owns the Question.
	 */
	public function section()
	{
		return $this->belongsTo('App\Models\Section');
	}

	/**
	 * Get the answers for the Question.
	 */
	public function answers()
	{
		return $this->hasMany('App\Models\Answer');
	}

	/**
	 * Get the question type of the Question.
	 */
	public function type()
	{
		return $this->belongsTo('App\Models\QuestionType');
	}

	/**
	 * Get all of the Question's meta data
	 */
	public function meta()
	{
		return $this->morphMany('App\Models\Meta', 'metable');
	}
}
