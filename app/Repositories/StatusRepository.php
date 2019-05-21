<?php

namespace App\Repositories;

use App\Models\Status;

class StatusRepository {
    private $statusesById = [];
    private $statusesByName = [];

    public function __construct()
    {
        $statuses = Status::all();

        $this->statusesById = $statuses->mapWithKeys(function($item) {
            return [$item['id'] => $item];
        });
        $this->statusesByName = $statuses->mapWithKeys(function($item) {
            return [$item['status'] => $item];  
        });
    }

    public function idByName($name, $default = null)
    {
        return data_get($this->statusesByName, "$name.id", $default);
    }

    public function nameById($id, $default = null)
    {
        return data_get($this->statusesById, "$id.status", $default);
    }

    public function dictionary($id)
    {
        return [
            'value' => $id,
            'label' => $this->nameById($id),
        ];
    }
}