<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Question extends Model
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

		static::deleting(function ($question) {
			$question->answers->each(function ($answer) {
				$answer->delete();
			});

			$question->responses->each(function ($response) {
				$response->delete();
			});

			$question->validations->each(function ($validation) {
				$validation->delete();
			});
		});
	}

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'question', 'description', 'mandatory', 'section_id', 'question_type_id', 'order', 'width'
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
	 * Get the triggers for the Question.
	 */
	public function triggers()
	{
		return $this->hasMany('App\Models\QuestionTrigger', 'question_id');
	}

	/**
	 * Get the responses for the Question.
	 */
	public function responses()
	{
		return $this->hasMany('App\Models\Response');
	}

	/**
	 * Get the validations for the Question.
	 */
	public function validations()
	{
		return $this->hasMany('App\Models\Validation');
	}

	/**
	 * Get the question type of the Question.
	 */
	public function questionType()
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
