<?php

namespace App\Http\Controllers;

use Auth;
use Queue;
use Illuminate\Http\Request;
use App\Jobs\FormUploadJob;
use App\Services\FileStoreService;

class FormUploadController extends Controller {

    private $fileStoreService;

    public function __construct() {
        $this->middleware('auth:api');
        $this->fileStoreService = new FileStoreService;
    }

    public function store(Request $request) {
        // Store File
        $file = $this->fileStoreService
            ->uploadAs($request->file('file'), $request->file('file')->getClientOriginalName(), 'local');

        // Set Job Params
        $data['file'] = $file['path'];
        $data['user_id'] = Auth::user()->id;
        $data['application_slug'] = $request->route('application_slug');
        $data['form_template_id'] = $request->route('id');
        $data['where'] = json_decode($request->input('where', null));
        
        dispatch(new FormUploadJob($data));
        return response()->json(['upload' => 'The form data has been uploaded for import.']);
    }
}