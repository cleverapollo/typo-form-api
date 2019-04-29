<?php

namespace App\Events;

use App\Models\Invitation;
use App\User;

class InvitationAccepted extends Event
{
    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(User $user, Invitation $invitation)
    {
        $this->user = $user;
        $this->invitation = $invitation;
    }
}
