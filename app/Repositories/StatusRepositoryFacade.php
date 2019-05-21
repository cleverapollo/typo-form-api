<?php

namespace App\Repositories;

use Illuminate\Support\Facades\Facade;

class StatusRepositoryFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return StatusRepository::class;
    }
}