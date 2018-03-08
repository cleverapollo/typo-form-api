<?php

namespace App\Http\Controllers;

use Laravel\Lumen\Routing\Controller as BaseController;
use App\Http\Foundation\Auth\Access\AuthorizesRequests;

class Controller extends BaseController
{
    use AuthorizesRequests;

    /**
     * Generate error message for status and action
     *
     * @param $data
     * @param $status
     * @param $action
     * @return string
     */
    protected function generateErrorMessage($data, $status, $action) {
        if ($status == 404) {
            return 'There is no ' . $data . ' with this ID.';
        }
        return 'Failed to ' . $action . ' '. $data . '. Please try again later.';
    }
}
