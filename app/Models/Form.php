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
            $form->responses->each(function ($response) {
				$response->delete();
			});
		});
	}

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'form_template_id', 'user_id', 'organisation_id', 'progress', 'period_start', 'period_end', 'status_id', 'submitted_date'
	];

	/**
	 * Get the form_template that owns the Form.
	 */
	public function form_template()
	{
		return $this->belongsTo('App\Models\FormTemplate');
	}

	/**
	 * Get the user that owns the Form.
	 */
	public function user()
	{
		return $this->belongsTo('App\User');
	}

	/**
	 * Get the Organisation that owns the Form.
	 */
	public function organisation()
	{
		return $this->belongsTo('App\Models\Organisation');
	}

	/**
	 * Get the status that owns the Form.
	 */
	public function status()
	{
		return $this->belongsTo('App\Models\Status');
	}

	/**
	 * Get the responses for the Form.
	 */
	public function responses()
	{
		return $this->hasMany('App\Models\Response');
	}

	/**
	 * Get all of the Form's meta data
	 */
	public function metas()
	{
		return $this->morphMany('App\Models\Meta', 'metable');
	}

    /**
     * Get all of the Form's logs.
     */
    public function logs()
    {
        return $this->morphMany('App\Models\Log', 'resourcable');
    }

    /**
     * Get all of the Form's notes.
     */
    public function notes()
    {
        return $this->morphMany('App\Models\Note', 'recordable');
    }
}
