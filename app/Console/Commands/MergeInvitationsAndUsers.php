<?php

namespace App\Console\Commands;

use \ApplicationUserRepository;
use \DB;
use \OrganisationUserRepository;
use \Storage;
use \UserRepository;
use \UserStatusRepository;
use App\Models\ApplicationUser;
use App\Models\Invitation;
use App\Models\OrganisationUser;
use App\Models\WorkflowJob;
use App\User;
use Illuminate\Console\Command;

class MergeInvitationsAndUsers extends Command {
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'informed365:merge-invitations-and-users';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "A one off task to merge invitations and users";
    
    protected function log($message) 
    {
        Storage::append('merge.log', is_string($message) ? $message : json_encode($message));
    }

    public function handle()
    {
        if (!$this->confirm('This should only be ran ONCE per environment. Do you wish to continue?')) {
            return;
        }

        $this->log('-- Running Merge ---------------------------------');
        $this->log('Begin transaction');
        DB::beginTransaction();

        $this->log('');

        $this->log("== Updating existing users =============================");
        User::all()->each(function($user) {
            $user->update([
                'status' => UserStatusRepository::idByLabel('Registered'),
            ]);
        });
        $this->log("Successfully set all existing users to Registered");

        ApplicationUser::all()->each(function($applicationUser) {
            $applicationUser->update([
                'status' => UserStatusRepository::idByLabel('Active'),
            ]);
        });
        $this->log("Successfully set all existing ApplicationUsers to Active");

        OrganisationUser::all()->each(function($organisationUser) {
            $organisationUser->update([
                'status' => UserStatusRepository::idByLabel('Active'),
            ]);
        });
        $this->log("Successfully set all existing OrganisationUsers to Active");

        Invitation::where('email', 'NOT ILIKE', 'MIGRATED__@__%')->each(function($invite) {
            $this->log("== Processing invite =============================");
            $this->log($invite);

            $existingUser = User::whereEmail($invite->email)->first();

            // Existing user
            if($existingUser) {
                $this->log('User was existing');
                $this->log($existingUser);

                // Existing user + Application Invite
                if($invite->type_id === 1) {
                    $applicationId = $invite->reference_id;
                    $this->log("Invite was for application: $applicationId");
                    $applicationUser = ApplicationUserRepository::find($applicationId, $existingUser->id);
                                        
                    // Existing user + Application Invite + Not Existing ApplicationUser
                    if(!$applicationUser) {
                        $this->log("Not Existing ApplicationUser.");
                        $applicationUser = ApplicationUserRepository::inviteUser($applicationId, $existingUser->id, $invite->role_id, $invite->inviter_id, $invite->meta);

                        $applicationUser = ApplicationUserRepository::find($applicationId, $existingUser->id);
                        $applicationUser->status = $invite->status
                            ? UserStatusRepository::idByLabel('Active')
                            : UserStatusRepository::idByLabel('Invited');
                        $applicationUser->save();

                        $this->log("Created ApplicationUser:");
                        $this->log($applicationUser);
                    } 
                    
                    // Existing user + Application Invite + Existing ApplicationUser
                    else {
                        $this->log("Existing ApplicationUser:");
                        $this->log($applicationUser);
                        $applicationUser->meta = array_merge(['inviter_id' => $invite->inviter_id, 'invite' => $invite->meta], $applicationUser->meta ?? []);
                        $applicationUser->save();
                        $this->log("Updated ApplicationUser:");
                        $this->log($applicationUser);
                    }
                }

                // Existing user + Organisation Invite
                else if($invite->type_id === 2) {
                    $organisationId = $invite->reference_id;
                    $this->log("Invite was organisation: $organisationId");
                    $organisationUser = OrganisationUserRepository::find($organisationId, $existingUser->id);
                                        
                    // Existing user + Organisation Invite + Not Existing OrganisationUser
                    if(!$organisationUser) {
                        $this->log("Not Existing OrganisationUser.");
                        $organisationUser = OrganisationUserRepository::inviteUser($organisationId, $existingUser->id, $invite->role_id, $invite->inviter_id, $invite->meta);
                        
                        $organisationUser = OrganisationUserRepository::find($organisationId, $existingUser->id);
                        $organisationUser->status = $invite->status
                            ? UserStatusRepository::idByLabel('Active')
                            : UserStatusRepository::idByLabel('Invited');
                        $organisationUser->save();
                        $this->log("Created OrganisationUser:");
                        $this->log($organisationUser);
                    } 

                    // Existing user + Organisation Invite + Existing OrganisationUser
                    else {
                        $this->log("Existing OrganisationUser:");
                        $this->log($organisationUser);
                        $organisationUser->meta = array_merge(['inviter_id' => $invite->inviter_id, 'invite' => $invite->meta], $organisationUser->meta ?? []);
                        $organisationUser->save();
                        $this->log("Updated OrganisationUser:");
                        $this->log($organisationUser);
                    }
                }

            // Not Existing user
            } else {
                $this->log('User for this invite did not exist. Create an unregistered account for them.');
                $user = UserRepository::createUnregisteredUser($invite->first_name, $invite->last_name, $invite->email, $invite->role_id);
                
                // Not Existing user + Application Invite
                if($invite->type_id === 1) {
                    $applicationId = $invite->reference_id;                    
                    $this->log("Invite was for application: $applicationId");
                   
                    $applicationUser = ApplicationUserRepository::inviteUser($applicationId, $user->id, $invite->role_id, $invite->inviter_id, $invite->meta);

                    $applicationUser = ApplicationUserRepository::find($applicationId, $user->id);
                    $applicationUser->status = $invite->status
                        ? UserStatusRepository::idByLabel('Active')
                        : UserStatusRepository::idByLabel('Invited');
                    $applicationUser->save();
                    $this->log("Invited. Created ApplicationUser:");
                    $this->log($applicationUser);
                }

                // Not Existing user + Organisation Invite
                else if($invite->type_id === 2) {
                    $organisation = $invite->reference_id;                    
                    $this->log("Invite was for organisation: $organisation");
                   
                    $organisationUser = OrganisationUserRepository::inviteUser($organisation, $user->id, $invite->role_id, $invite->inviter_id, $invite->meta);

                    $organisationUser = OrganisationUserRepository::find($applicationId, $user->id);
                    $organisationUser->status = $invite->status
                            ? UserStatusRepository::idByLabel('Active')
                            : UserStatusRepository::idByLabel('Invited');
                    $organisationUser->save();
                    $this->log("Invited. Created OrganisationUser:");
                    $this->log($organisationUser);
                }
            }

            $invite->email = 'MIGRATED__@__' . $invite->email; 
            $invite->save();
            $this->log("Invite marked as migrated:");
            $this->log($invite);
            $this->log('');
        });

        WorkflowJob::whereStatus(1)->get()->each(function($workflowJob) {
            $invite = Invitation::find($workflowJob->transaction_id);
            $email = str_replace('migrated__@__', '', $invite->email);
            $user = User::whereEmail($email)->first();
            
            $applicationId = $workflowJob->workflow->application_id;
            
            $applicationUser = ApplicationUser::whereApplicationId($applicationId)->whereUserId($user->id)->first();
            
            $workflowJob->transaction_id = $applicationUser->id;
            $workflowJob->save();
        });

        DB::commit();
    }
}