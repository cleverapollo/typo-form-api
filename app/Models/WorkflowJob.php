<?php

namespace App\Models;

use App\Models\Workflow;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WorkflowJob extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'completed_at',
        'data',
        'scheduled_for',
        'message',
        'signature',
        'transaction_id',
        'workflow_id',
    ];

    protected $dates = [
        'scheduled_for', 'completed_at'
    ];

    public function workflow() 
    {
        return $this->belongsTo(Workflow::class);
    }
}
