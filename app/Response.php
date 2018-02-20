<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Response extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'response', 'submission_id', 'answer_id'
    ];
    /**
     * Get the Submission that owns the Response.
     */
    public function submission()
    {
        return $this->belongsTo('App\Submission');
    }
    /**
     * Get the Answer that owns the Response.
     */
    public function answer()
    {
        return $this->belongsTo('App\Answer');
    }
}
