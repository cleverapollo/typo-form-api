<?php

namespace App\Repositories;

use App\Models\UserStatus;

class UserStatusRepository extends DictionaryRepository {
    public function __construct()
    {
        parent::__construct(UserStatus::all(), 'id', 'label');
    }
}