<?php

namespace App\Models;

use App\Models\Workflow;
use Illuminate\Database\Eloquent\Model;

class WorkflowJob extends Model
{
    protected $fillable = [
        'completed_at',
        'data',
        'scheduled_for',
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
