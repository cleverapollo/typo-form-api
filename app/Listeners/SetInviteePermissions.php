<?php

namespace App\Listeners;

use \Acl;
use \Log;
use App\Events\InvitationAccepted;
use App\Models\FormTemplate;

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
        if(!isset($event->invitation->meta['form_templates'])) {
            Log::warning("No form_templates selected for invitation. Invitation id: {$event->invitation->id}");
            return;
        }

        $formTemplateIds = $event->invitation->meta['form_templates'];

        Acl::canShowFormTemplate($event->user, $formTemplateIds);
        Acl::canStoreFormTemplate($event->user, $formTemplateIds);
    }
}
