<?php

namespace App\Repositories;

use App\Models\Role;

class RoleRepository {
    private $rolesById = [];
    private $rolesByName = [];

    public function __construct()
    {
        $roles = Role::all();

        $this->rolesById = $roles->mapWithKeys(function($item) {
            return [$item['id'] => $item];
        });
        $this->rolesByName = $roles->mapWithKeys(function($item) {
            return [$item['name'] => $item];  
        });
    }

    public function idByName($name, $default = null)
    {
        return data_get($this->rolesByName, "$name.id", $default);
    }

    public function nameById($id, $default = null)
    {
        return data_get($this->rolesById, "$id.name", $default);
    }

    public function dictionary($id)
    {
        return [
            'value' => $id,
            'label' => $this->nameById($id),
        ];
    }
}