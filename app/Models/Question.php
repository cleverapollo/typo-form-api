<?php

namespace App\Models;

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
     * Get the section that owns the Question.
     */
    public function section()
    {
        return $this->belongsTo('App\Models\Section');
    }

    /**
     * Get the answers for the Question.
     */
    public function answer()
    {
        return $this->hasMany('App\Models\Answer');
    }
}
