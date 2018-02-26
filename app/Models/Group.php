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
        'name', 'section_id'
    ];
    /**
     * Get the section that owns the group.
     */
    public function section()
    {
        return $this->belongsTo('App\Section');
    }
}
