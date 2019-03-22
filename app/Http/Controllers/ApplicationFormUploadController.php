<?php

namespace App\Http\Controllers;

use Auth;
use Illuminate\Http\Request;
use App\Jobs\ApplicationFormUploadJob;
use App\Services\ExcelService;

class ApplicationFormUploadController extends Controller {

    private $fileStoreService;
    private $excelService;

    public function __construct() {
        $this->middleware('auth:api');
        $this->excelService = new ExcelService;
    }

    public function store(Request $request) {
        $data = $this->excelService->toArray($request->file('file')->getRealPath());
        
        // Set Job Params
        $data['data'] = $data;
        $data['user_id'] = Auth::user()->id;
        $data['application_slug'] = $request->route('application_slug');
        
        dispatch(new ApplicationFormUploadJob($data));
        return response()->json(['upload' => 'The form data has been uploaded for import.']);
    }
}