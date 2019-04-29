<?php

namespace App\Listeners;

use \Acl;
use \Log;
use App\Events\InvitationAccepted;
use App\Services\AclService;

class SetInviteePermissions
{
    /**
     * Map captured form_templates in invitation meta to Bouncer level permissions
     *
     * @param  InvitationAccepted $event
     * @return void
     */
    public function handle(InvitationAccepted $event)
    {
        // An invitation can assign "show" abilities for a particular form template to a user.
        // Here we extract those abilities from the invites meta data and request the user
        // be given those abilities
        //
        $formTemplateIds = $event->invitation->meta['form_templates'] ?? [];
        Acl::allowAccessToResource($event->user, 'form_templates', $formTemplateIds, AclService::SHOW);
    }
}
