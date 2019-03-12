<?php

namespace App\Services;

class Service {
    
    public function logError($e) {
        errorLog(
            $e->getMessage() . '. ' . 
            $e->getFile() . ' on line ' . $e->getLine() . '. ' .
            $e->getCode() . '.');
    }
}