<?php

namespace App\Http\Controllers;

use Auth;
use Illuminate\Http\Request;
use App\Jobs\FormUploadJob;
use App\Services\ExcelService;

class FormUploadController extends Controller {

    private $excelService;

    public function __construct() {
        $this->middleware('auth:api');
        $this->excelService = new ExcelService;
    }

    public function store(Request $request) {
        ini_set('max_execution_time', 0);
        $data = $this->excelService->toArray($request->file('file')->getRealPath(), 1);

        foreach($data as $chunk) {
            $job = [];
            $job['data'] = $chunk;
            $job['user_id'] = Auth::user()->id;
            $job['application_slug'] = $request->route('application_slug');
            $job['form_template_id'] = $request->route('id');
            $job['where'] = json_decode($request->input('where', null));
            dispatch(new FormUploadJob($job));
        }

        return response()->json(['upload' => 'The form data has been uploaded for import.']);
    }
}