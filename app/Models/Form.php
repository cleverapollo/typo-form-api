<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Form extends Model
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

		static::deleting(function ($form) {
			$form->sections->each(function ($section) {
				$section->delete();
			});

			$form->submissions->each(function ($submission) {
				$submission->delete();
			});

			$form->validations->each(function ($validation) {
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
		'name', 'application_id', 'show_progress', 'auto'
	];

	/**
	 * Get the application that owns the Form.
	 */
	public function application()
	{
		return $this->belongsTo('App\Models\Application');
	}

	/**
	 * Get the period of the Form.
	 */
	public function period()
	{
		return $this->belongsTo('App\Models\Period');
	}

	/**
	 * Get the sections for the Form.
	 */
	public function sections()
	{
		return $this->hasMany('App\Models\Section');
	}

	/**
	 * Get the submissions for the Form.
	 */
	public function submissions()
	{
		return $this->hasMany('App\Models\Submission');
	}

	/**
	 * Get the validations for the Form.
	 */
	public function validations()
	{
		return $this->hasMany('App\Models\Validation');
	}

	/**
	 * Get the question triggers for the Form.
	 */
	public function triggers()
	{
		return $this->hasMany('App\Models\QuestionTrigger');
	}

	/**
	 * Get all of the Form's meta data
	 */
	public function meta()
	{
		return $this->morphMany('App\Models\Meta', 'metable');
	}
}
