<?php

namespace App;

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
     * Get the form that owns the section.
     */
    public function form()
    {
        return $this->belongsTo('App\Form');
    }
    /**
     * Get the groups for the Section.
     */
    public function group()
    {
        return $this->hasMany('App\Group');
    }
    /**
     * Get the groups for the Section.
     */
    public function question()
    {
        return $this->hasMany('App\Question');
    }
}
