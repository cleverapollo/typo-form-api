<?php

namespace App\Repositories;

use Illuminate\Support\Facades\Facade;

class OrganisationUserRepositoryFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return OrganisationUserRepository::class;
    }
}