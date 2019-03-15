<?php

namespace App\Http\Controllers;

use Auth;
use Illuminate\Http\Request;
use App\Services\FormTemplateService;

class FormTemplateFileController extends Controller {

    private $formTemplateService;

    public function __construct() {
        $this->middleware('auth:api');
        $this->formTemplateService = new FormTemplateService;
    }

    /**
     * Export Form Template to CSV
     *
     * @param Request $request
     * @return File
     */
    public function show(Request $request) {
        $file = $this->formTemplateService->export($request->route('id'));
        return $this->jsonResponse(['file' => $file]);
    }

    /**
     * Import Form Template from CSV
     *
     * @param Request $request
     * @return File
     */
    public function store(Request $request) {

    }
}