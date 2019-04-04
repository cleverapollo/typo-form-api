<?php

namespace App\Http\Controllers;

use Auth;
use Illuminate\Http\Request;
use App\Jobs\ApplicationFormUploadJob;
use App\Services\ExcelService;

class ApplicationFormUploadController extends Controller {

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
            dispatch(new ApplicationFormUploadJob($job));
        }
        
        return response()->json(['upload' => 'The form data has been uploaded for import.']);
    }
}