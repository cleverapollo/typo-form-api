<?php

namespace App\Services;

use App\Models\Application;

class ApplicationService extends Service {

    private $application;

    public function __construct() {
        $this->application = new Form;
    }
    
    public function create($data) {
        return $this->application
            ->create($data);
    }

    public function get($id) {
        return $this->application
            ->where('id', $id)
            ->first();
    }

    public function all() {
        return $this->application
            ->get();
    }

    public function update($id, $data) {
        return $this->application
            ->where('id', $id)
            ->update($data);
    }

    public function delete($id) {
        return $this->application
            ->where('id', $id)
            ->destroy();
    }

    public function getBySlug($slug) {
        return $this->application
            ->where('slug', $slug)
            ->first();
    }
}