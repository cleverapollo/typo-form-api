<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Team extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name'
    ];

    public function user()
    {
        return $this->belongsToMany('App\User', 'user_teams');
    }

    /**
     * Get the submissions for the Form.
     */
    public function submission()
    {
        return $this->hasMany('App\Models\Submission');
    }
}
