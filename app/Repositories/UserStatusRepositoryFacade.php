<?php

namespace App\Repositories;

use Illuminate\Support\Facades\Facade;

class UserStatusRepositoryFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return UserStatusRepository::class;
    }
}