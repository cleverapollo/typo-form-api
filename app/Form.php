<?php

namespace App;

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
        return $this->hasMany('App\Section');
    }
}
