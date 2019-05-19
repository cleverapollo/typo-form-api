<?php

namespace App\Repositories;

use App\Models\UserStatus;

class UserStatusRepository {
    private $userStatusesById = [];
    private $userStatusesByLabel = [];

    public function __construct()
    {
        $userStatuses = UserStatus::all();

        $this->userStatusesById = $userStatuses->mapWithKeys(function($item) {
            return [$item['id'] => $item];
        });

        $this->userStatusesByLabel = $userStatuses->mapWithKeys(function($item) {
            return [$item['label'] => $item];
        });
    }

    public function idByLabel($label, $default = null)
    {
        return data_get($this->userStatusesByLabel, "$label.id", $default);
    }

    public function labelById($id, $default = null)
    {
        return data_get($this->userStatusesById, "$id.label", $default);
    }

    public function dictionary($id)
    {
        return [
            'value' => $id,
            'label' => $this->labelById($id),
        ];
    }
}