<?php

namespace App\Services;
use Exception;
use Log;

class Service {
    
    /**
     * Log any errors
     *
     * @param Exception $e
     * @return void
     */
    public function logError($e) {
        Log::error(
            $e->getMessage() . '. ' . 
            $e->getFile() . ' on line ' . $e->getLine() . '. ' .
            $e->getCode() . '.');
    }
}