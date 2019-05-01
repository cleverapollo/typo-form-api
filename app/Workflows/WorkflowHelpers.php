<?php

namespace App\Workflows;

use App\Workflows\Triggers\ITrigger;

class WorkflowHelpers {

    static function resolveTrigger(string $triggerKey)
    {
        $triggerClass = "App\\Workflows\\Triggers\\{$triggerKey}Trigger";

        if(!class_exists($triggerClass)) {
            throw new \Exception("$triggerClass does not exist");
        }

        $instance = new $triggerClass();
        if(!$instance instanceof ITrigger) {
            throw new \Exception("$triggerClass does not implement ITrigger");
        }

        return $instance;
    }
}