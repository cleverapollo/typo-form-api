<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuestionType extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'type'
    ];

    /**
     * Get the questions for the Type.
     */
    public function questions()
    {
        return $this->hasMany('App\Models\Question');
    }
}
