<?php

namespace App\Repositories;

use Illuminate\Support\Facades\Facade;

class TypeRepositoryFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return TypeRepository::class;
    }
}