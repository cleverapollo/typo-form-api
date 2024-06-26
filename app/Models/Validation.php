<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Validation extends Model
{
	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'form_template_id', 'question_id', 'validation_type_id', 'validation_data'
	];

	/**
	 * Get the Form Template that owns the Validation.
	 */
	public function form_template()
	{
		return $this->belongsTo('App\Models\FormTemplate');
	}

	/**
	 * Get the question that owns the Validation.
	 */
	public function question()
	{
		return $this->belongsTo('App\Models\Question');
	}

	/**
	 * Get the validation type of the Validation.
	 */
	public function validationType()
	{
		return $this->belongsTo('App\Models\ValidationType');
	}

	/**
	 * Get all of the Validation's meta data
	 */
	public function metas()
	{
		return $this->morphMany('App\Models\Meta', 'metable');
	}

    /**
     * Get all of the Validation's logs.
     */
    public function logs()
    {
        return $this->morphMany('App\Models\Log', 'resourcable');
    }
}
