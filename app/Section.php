<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Section extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'order', 'form_id'
    ];
    /**
     * Get the form that owns the section.
     */
    public function post()
    {
        return $this->belongsTo('App\Form');
    }
}
