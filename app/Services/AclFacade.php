<?php

namespace App\Services;

use Illuminate\Support\Facades\Facade;

class AclFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return AclService::class;
    }
}