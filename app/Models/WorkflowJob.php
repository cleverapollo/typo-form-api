<?php

namespace App\Models;

use App\Models\Workflow;
use Illuminate\Database\Eloquent\Model;

class WorkflowJob extends Model
{
    protected $fillable = [
        'transaction_id', 'workflow_id', 'scheduled_for', 'completed_at', 'data',
    ];

    protected $dates = [
        'scheduled_for', 'completed_at'
    ];

    public function workflow() 
    {
        return $this->belongsTo(Workflow::class);
    }
}
