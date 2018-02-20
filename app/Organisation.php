<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Organisation extends Model
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
        return $this->belongsToMany('App\User', 'user_organisation');
    }
    /**
     * Get the submissions for the Form.
     */
    public function submission()
    {
        return $this->hasMany('App\Submission');
    }
}
