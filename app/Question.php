<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'question', 'order', 'section_id'
    ];
    /**
     * Get the form that owns the section.
     */
    public function section()
    {
        return $this->belongsTo('App\Section');
    }
    /**
     * Get the answer for the Question.
     */
    public function answer()
    {
        return $this->hasMany('App\Answer');
    }
}