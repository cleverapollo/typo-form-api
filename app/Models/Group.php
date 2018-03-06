<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'section_id', 'repeatable'
    ];

    /**
     * Get the section that owns the Group.
     */
    public function section()
    {
        return $this->belongsTo('App\Models\Section');
    }

    /**
     * Get all of the Group's meta data
     */
    public function meta()
    {
        return $this->morphMany('App\Models\Meta', 'metable');
    }
}
