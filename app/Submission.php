<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Submission extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'form_id', 'user_id', 'organisation_id'
    ];
    /**
     * Get the form that owns the submission.
     */
    public function form()
    {
        return $this->belongsTo('App\Form');
    }
    /**
     * Get the user that owns the submission.
     */
    public function user()
    {
        return $this->belongsTo('App\User');
    }
    /**
     * Get the organisation that owns the submission.
     */
    public function organisation()
    {
        return $this->belongsTo('App\Organisation');
    }
    /**
     * Get the answer for the Question.
     */
    public function response()
    {
        return $this->hasMany('App\Response');
    }
}
