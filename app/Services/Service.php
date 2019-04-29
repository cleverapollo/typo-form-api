<?php

namespace App\Services;
use Exception;
use Log;

class Service {
    
    /**
     * Convert email addresses from string to array
     *
     * @param String $email
     * @return string
     */
    public function formatEmailAddresses ($email) {
        $email = str_replace(' ', '', $email);
        $email = str_replace(';', ',', $email);
        $email = explode(',', $email);
        return $email;
    }
}