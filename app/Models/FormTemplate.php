<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FormTemplate extends Model
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

		static::deleting(function ($form_template) {
            $form_template->sections->each(function ($section) {
				$section->delete();
			});

            $form_template->forms->each(function ($form) {
                $form->delete();
			});

            $form_template->validations->each(function ($validation) {
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
	 * Get the application that owns the FormTemplate.
	 */
	public function application()
	{
		return $this->belongsTo('App\Models\Application');
	}

	/**
	 * Get the sections for the FormTemplate.
	 */
	public function sections()
	{
		return $this->hasMany('App\Models\Section');
	}

	/**
	 * Get the forms for the FormTemplate.
	 */
	public function forms()
	{
		return $this->hasMany('App\Models\Form');
	}

	/**
	 * Get the validations for the FormTemplate.
	 */
	public function validations()
	{
		return $this->hasMany('App\Models\Validation');
	}

	/**
	 * Get the question triggers for the FormTemplate.
	 */
	public function triggers()
	{
		return $this->hasMany('App\Models\QuestionTrigger');
	}

	/**
	 * Get all of the FormTemplate's meta data
	 */
	public function metas()
	{
		return $this->morphMany('App\Models\Meta', 'metable');
	}

    /**
     * Get all of the FormTemplate's logs.
     */
    public function logs()
    {
        return $this->morphMany('App\Models\Log', 'resourcable');
    }
}
