<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TriggerType extends Model
{
	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'question_type_id', 'comparator_id', 'answer', 'value'
	];

	/**
	 * Get the question type of the Trigger type.
	 */
	public function questionType()
	{
		return $this->belongsTo('App\Models\QuestionType');
	}

	/**
	 * Get the comparator of the Trigger type.
	 */
	public function comparator()
	{
		return $this->belongsTo('App\Models\Comparator');
	}
}
