<?php

namespace App\Repositories;

use App\Models\Type;

abstract class DictionaryRepository {
    private $itemsById = [];
    private $itemsByName = [];

    public function __construct($items, $idField, $nameField)
    {
        $this->idField = $idField;
        $this->nameField = $nameField;

        $this->itemsById = $items->mapWithKeys(function($item) use ($idField) {
            return [$item[$idField] => $item];
        });
        $this->itemsByName = $items->mapWithKeys(function($item) use ($nameField) {
            return [$item[$nameField] => $item];  
        });
    }

    public function idByName($name, $default = null)
    {
        return data_get($this->itemsByName, "$name.{$this->idField}", $default);
    }

    public function idByLabel($name, $default = null)
    {
        return $this->idByName($name, $default);
    }

    public function nameById($id, $default = null)
    {
        return data_get($this->itemsById, "$id.{$this->nameField}", $default);
    }

    public function dictionary($id)
    {
        return [
            'value' => $id,
            'label' => $this->nameById($id),
        ];
    }
}