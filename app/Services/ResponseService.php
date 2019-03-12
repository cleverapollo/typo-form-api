<?php

namespace App\Services;

use App\Models\Response;

class ResponseService extends Service {
    
    private $response;

    public function __construct() {
        $this->response = new Response;
    }
    
    public function create($data) {
        return $this->response
            ->create($data);
    }

    public function get($id) {
        return $this->response
            ->where('id', $id)
            ->first();
    }

    public function all() {
        return $this->response
            ->get();
    }

    public function update($id, $data) {
        return $this->response
            ->where('id', $id)
            ->update($data);
    }

    public function delete($id) {
        return $this->response
            ->where('id', $id)
            ->destroy();
    }

    public function deleteFormResponses($form_id) {
        return $this->response
            ->where('form_id', $form_id)
            ->destroy();
    }
}