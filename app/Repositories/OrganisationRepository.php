<?php

namespace App\Repositories;

use App\Models\Organisation;

class OrganisationRepository {
    
    public function firstOrCreate($name, $applicationId)
    {
        return Organisation::firstOrCreate(['name' => $name, 'application_id' => $applicationId]);
    }
}