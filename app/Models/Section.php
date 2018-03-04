<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Section extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'order', 'form_id'
    ];

    /**
     * Get the form that owns the Section.
     */
    public function form()
    {
        return $this->belongsTo('App\Models\Form');
    }

    /**
     * Get the groups for the Section.
     */
    public function group()
    {
        return $this->hasMany('App\Models\Group');
    }

    /**
     * Get the questions for the Section.
     */
    public function question()
    {
        return $this->hasMany('App\Models\Question');
    }
}
