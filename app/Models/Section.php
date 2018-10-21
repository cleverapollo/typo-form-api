<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Section extends Model
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

		static::deleting(function ($section) {
			$section->children->each(function ($section) {
				$section->delete();
			});

			$section->questions->each(function ($question) {
				$question->delete();
			});
		});
	}

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'name', 'form_id', 'parent_section_id', 'order', 'repeatable', 'max_rows', 'min_rows'
	];

	/**
	 * Get the form that owns the Section.
	 */
	public function form()
	{
		return $this->belongsTo('App\Models\Form');
	}

	/**
	 * Get the questions for the Section.
	 */
	public function questions()
	{
		return $this->hasMany('App\Models\Question');
	}

	/**
	 * Get the parent section for the Section.
	 */
	public function parent()
	{
		return $this->belongsTo('App\Models\Section', 'parent_section_id');
	}

	/**
	 * Get the children sections for the Section.
	 */
	public function children()
	{
		return $this->hasMany('App\Models\Section', 'parent_section_id');
	}

	/**
	 * Get all of the Section's meta data
	 */
	public function metas()
	{
		return $this->morphMany('App\Models\Meta', 'metable');
	}

	/**
	 * Get the triggers for the Section.
	 */
	public function triggers()
	{
		return $this->hasMany('App\Models\QuestionTrigger', 'question_id');
	}
}
