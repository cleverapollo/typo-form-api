<?php

namespace App\Repositories;

use App\Models\Status;

class StatusRepository extends DictionaryRepository {
    public function __construct()
    {
        parent::__construct(Status::all(), 'id', 'status');
    }
}