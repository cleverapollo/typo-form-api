<?php

namespace App\Http\Controllers;

use Auth;
use Illuminate\Http\Request;
use App\Jobs\FormTemplateUploadJob;
use App\Services\ExcelService;

class FormTemplateUploadController extends Controller {

    private $excelService;

    public function __construct() {
        $this->middleware('auth:api');
        $this->excelService = new ExcelService;
    }

    public function store(Request $request) {
        $data = $this->excelService->toArray($request->file('file')->getRealPath(), 1);

        foreach($data as $chunk) {
            $job = [];
            $job['data'] = $chunk;
            $job['user_id'] = Auth::user()->id;
            $job['application_slug'] = $request->route('application_slug');
            $job['form_template_id'] = $request->route('id');
            dispatch(new FormTemplateUploadJob($job));
        }

        return response()->json(['upload' => 'The form template data has been uploaded for import.']);
    }
}