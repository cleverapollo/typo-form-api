<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccessSettings extends Model
{
    protected $fillable = [
        'access_level_id'
    ];

    public function resource()
    {
        return $this->morphTo();
    }

    public function accessLevel()
    {
        return $this->belongsTo(AccessLevel::class);
    }
}
