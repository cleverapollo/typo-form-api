<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Note extends Model
{
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'note_type_id', 'description', 'note', 'user_id', 'recordable_id', 'recordable_type', 'task', 'task_due_at', 'completed'
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
