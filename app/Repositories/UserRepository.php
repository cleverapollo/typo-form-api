<?php

namespace App\Repositories;

use \UserStatusRepository;
use App\User;
use Illuminate\Support\Str;

class UserRepository {
    public function __construct() {
        $this->hash = app('hash');
    }

    /**
     * Create a new, unregistered user. This is most likely triggered by system events and on behalf
     * of the user, without them registering directly. For this reason, we create a temporary 
     * random password
     *
     * @param string $firstname
     * @param string $lastname
     * @param string $email
     * @param int $roleId
     * @param string $password - a pre-hashed password string
     * @return void
     */
    public function createUnregisteredUser($firstname, $lastname, $email, $roleId, $password = null)
    {
        // We create a purely random password that will need to be "reset" during registration. 
        if(is_null($password)) {
            $password = Str::random(40);
        } 

        return User::create([
            'first_name' => $firstname,
            'last_name' => $lastname,
            'email' => $email,
            'password' => $this->hash->make($password),
            'role_id' => $roleId,
            'status' => UserStatusRepository::idByLabel('Unregistered'),
        ]);
    }

    /**
     * Take an existing user and mark them as registered
     *
     * @param User $user
     * @param string $firstname
     * @param string $lastname
     * @param string $password - unhashed password string
     * @return void
     */
    public function registerUser($user, $firstname, $lastname, $password)
    {
        // Do not allow a registered user to change firstname/lastname/password via the registration
        // flow
        if($user->status !== UserStatusRepository::idByLabel('Unregistered')) {
            throw new Exception('Already registered');
        }

        return $user->update([
            'first_name' => $firstname,
            'last_name' => $lastname,
            'password' => $this->hash->make($password),
            'status' => UserStatusRepository::idByLabel('Registered'),
        ]);
    }
}
