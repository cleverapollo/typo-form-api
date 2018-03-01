<?php

namespace App\Http\Controllers;

use App\ResetsPasswords;

class PasswordController extends Controller
{
    use ResetsPasswords;

    public function __construct()
    {
        parent::__construct();

        $this->broker = 'users';
    }
}
