<?php

namespace App\Http\Controllers;

use Auth;
use Queue;
use Illuminate\Http\Request;
use App\Jobs\FormUploadJob;

class FormUploadController extends Controller {

    public function __contstruct() {
        $this->middleware('auth:api');
    }

    public function store(Request $request) {
        $data['file'] = $request->file('file')->getRealPath();
        $data['user_id'] = Auth::user()->id;
        $data['application_slug'] = $request->route('application_slug');
        $data['form_template_id'] = $request->route('id');
        $data['where'] = json_decode($request->input('where', null));
        
        response()->json(['upload' => 'The form data has been uploaded for import.']);
        return dispatch(new FormUploadJob($data));
    }
}