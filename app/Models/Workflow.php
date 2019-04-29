<?php

namespace App\Models;

use App\User;
use Illuminate\Database\Eloquent\Model;

class Workflow extends Model
{
    protected $fillable = [
        'name', 'author_id', 'trigger', 'trigger_config', 'action', 'action_config', 'delay', 
        'status', 'active_from', 'active_to',
    ];

    public function author()
    {
        return $this->belongsTo(User::class);
    }
}
