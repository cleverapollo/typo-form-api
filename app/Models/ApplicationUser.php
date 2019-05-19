<?php

namespace App\Models;

use App\User;
use Illuminate\Database\Eloquent\Relations\Pivot;

class ApplicationUser extends Pivot
{
    protected $table = 'application_users';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id', 'application_id', 'role_id', 'meta', 'status'
    ];

    protected $casts = [
        'meta' => 'array'
    ];
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    public function application()
    {
        return $this->belongsTo(Application::class);
    }

    /**
     * Get the role of the User in Application
     */
    public function role()
    {
        return $this->belongsTo('App\Models\Role');
    }

    /**
     * Get all of the ApplicationUser's logs.
     */
    public function logs()
    {
        return $this->morphMany('App\Models\Log', 'resourcable');
    }
}
