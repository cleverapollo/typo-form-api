<?php

namespace App\Events;

use App\Models\ApplicationUser;
use App\User;

class InvitationAccepted extends Event
{
    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(User $user, ApplicationUser $invitation)
    {
        $this->user = $user;
        $this->invitation = $invitation;
    }
}
