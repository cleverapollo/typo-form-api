<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Submission extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'form_id', 'user_id', 'team_id'
    ];

    /**
     * Get the form that owns the submission.
     */
    public function form()
    {
        return $this->belongsTo('App\Models\Form');
    }

    /**
     * Get the user that owns the submission.
     */
    public function user()
    {
        return $this->belongsTo('App\User');
    }

    /**
     * Get the team that owns the submission.
     */
    public function team()
    {
        return $this->belongsTo('App\Models\Team');
    }

    /**
     * Get the answer for the Question.
     */
    public function response()
    {
        return $this->hasMany('App\Models\Response');
    }
}
