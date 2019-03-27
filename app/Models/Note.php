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
        'event', 'note', 'created_by', 'recordable_id', 'recordable_type'
    ];

    /**
     * Get all of the owning logable models
     */
    public function recordable()
    {
        return $this->morphTo();
    }
}
