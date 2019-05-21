<?php

namespace App\Repositories;

use App\Models\Type;

class TypeRepository extends DictionaryRepository {
    public function __construct()
    {
        parent::__construct(Type::all(), 'id', 'name');
    }
}