<?php

namespace App\Events;

use App\Models\Invitation;
use App\User;

class InvitationSent extends Event
{
    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }
}
