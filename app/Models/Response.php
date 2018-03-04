<?php

namespace App\Models;

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
     * Get the submission that owns the Response.
     */
    public function submission()
    {
        return $this->belongsTo('App\Models\Submission');
    }

    /**
     * Get the answer that owns the Response.
     */
    public function answer()
    {
        return $this->belongsTo('App\Models\Answer');
    }
}
