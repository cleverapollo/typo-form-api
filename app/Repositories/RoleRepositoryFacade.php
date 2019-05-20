<?php

namespace App\Repositories;

use Illuminate\Support\Facades\Facade;

class RoleRepositoryFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return RoleRepository::class;
    }
}