<?php

namespace App\Models;

use App\User;
use Illuminate\Database\Eloquent\Model;

class Workflow extends Model
{
    protected $fillable = [
        'action_config',
        'action',
        'active_from',
        'active_to',
        'application_id',
        'author_id',
        'delay',
        'name',
        'status',
        'trigger_config',
        'trigger',
    ];

    public function author()
    {
        return $this->belongsTo(User::class);
    }
}
