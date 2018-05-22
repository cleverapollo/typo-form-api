<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuestionType extends Model
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
	 * Get the questions for the Question Type.
	 */
	public function questions()
	{
		return $this->hasMany('App\Models\Question');
	}

	/**
	 * Get the trigger types for the Question Type.
	 */
	public function triggerTypes()
	{
		return $this->hasMany('App\Models\TriggerType');
	}
}
