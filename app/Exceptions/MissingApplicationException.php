<?php

namespace App\Exceptions;

class MissingApplicationException extends ApiException
{
    public function __construct($message = 'There is no application with this name.')
    {
        parent::__construct(404, $message);
    }
}
