<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Application extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name'
    ];

    /**
     * Get the forms for the Application.
     */
    public function form()
    {
        return $this->hasMany('App\Models\Form');
    }

    /**
     * Get the teams for the Application.
     */
    public function team()
    {
        return $this->hasMany('App\Models\Team');
    }

    /**
     * Get all of the Application's meta data
     */
    public function meta()
    {
        return $this->morphMany('App\Models\Meta', 'metable');
    }
}
