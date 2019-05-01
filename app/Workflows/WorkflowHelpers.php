<?php

namespace App\Workflows;

use App\Workflows\Triggers\ITrigger;

class WorkflowHelpers {

    static function resolveTrigger(string $triggerKey, callable $logWarning)
    {
        $triggerClass = "App\\Workflows\\Triggers\\{$triggerKey}Trigger";

        if(!class_exists($triggerClass)) {
            $logWarning("$triggerClass does not exist");
            return null;
        }

        $instance = new $triggerClass();
        if(!$instance instanceof ITrigger) {
            $logWarning("$triggerClass does not implement ITrigger");
            return null;
        }

        return $instance;
    }
}