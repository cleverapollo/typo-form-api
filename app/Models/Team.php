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

    /**
     * Get the users that belongs to the Team.
     */
    public function user()
    {
        return $this->belongsToMany('App\User', 'user_teams');
    }

    /**
     * Get the submissions for the Team.
     */
    public function submission()
    {
        return $this->hasMany('App\Models\Submission');
    }

    /**
     * Get the application that owns the Team.
     */
    public function application()
    {
        return $this->belongsTo('App\Models\Application');
    }
}
