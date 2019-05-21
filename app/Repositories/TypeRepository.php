<?php

namespace App\Repositories;

use App\Models\Type;

class TypeRepository {
    private $typesById = [];
    private $typesByName = [];

    public function __construct()
    {
        $types = Type::all();

        $this->typesById = $types->mapWithKeys(function($item) {
            return [$item['id'] => $item];
        });
        $this->typesByName = $types->mapWithKeys(function($item) {
            return [$item['name'] => $item];  
        });
    }

    public function idByName($name, $default = null)
    {
        return data_get($this->typesByName, "$name.id", $default);
    }

    public function nameById($id, $default = null)
    {
        return data_get($this->typesById, "$id.name", $default);
    }

    public function dictionary($id)
    {
        return [
            'value' => $id,
            'label' => $this->nameById($id),
        ];
    }
}