<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Form extends Model
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
     * Get the sections for the Form.
     */
    public function section()
    {
        return $this->hasMany('App\Models\Section');
    }

    /**
     * Get the submissions for the Form.
     */
    public function submission()
    {
        return $this->hasMany('App\Models\Submission');
    }

    /**
     * Get the application that owns the Form.
     */
    public function application()
    {
        return $this->belongsTo('App\Models\Application');
    }
}
