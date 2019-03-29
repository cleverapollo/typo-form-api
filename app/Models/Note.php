<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Note extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'event', 'note', 'user_id', 'recordable_id', 'recordable_type'
    ];

    /**
     * Get the user that owns the Form.
     */
    public function user()
    {
        return $this->belongsTo('App\User');
    }

    /**
     * Get all of the owning recordable models
     */
    public function recordable()
    {
        return $this->morphTo();
    }
}
