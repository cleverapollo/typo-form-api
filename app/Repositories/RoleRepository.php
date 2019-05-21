<?php

namespace App\Repositories;

use App\Models\Role;

class RoleRepository extends DictionaryRepository {
    public function __construct()
    {
        parent::__construct(Role::all(), 'id', 'name');
    }
}