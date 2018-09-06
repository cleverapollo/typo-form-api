<?php

namespace App\Http\Controllers;

use Exception;
use Storage;
use Illuminate\Http\Request;

class FileController extends Controller
{
    
	/**
	 * Store File
	 *
	 * @param  Request $request
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function store(Request $request)
	{
		try {
            $file = [];
            $path = Storage::putFile('uploads', $request->file('file'));
            $file['size'] = Storage::size($path);
            $file['name'] = $request->file->getClientOriginalName();
            $file['url'] = Storage::url($path);
            $file['stored_name'] = basename($path);
			return $this->returnSuccessMessage('file', $file);
		} catch (Exception $e) {
			return $this->returnErrorMessage(503, "Error uploading file.");
		}
    }
    
    /**
     * Destroy File
     *
     * @param Request $request
	 * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Request $request) {
        try {
            $file = '/uploads/' . $request->input('name');
            $result = Storage::delete($file);
            return $this->returnSuccessMessage('result', $result);
        } catch(Exception $e) {
            return $this->returnErrorMessage(503, $e->getMessage());
        }
    }

    /**
     * Download File
     *
     * @param string $name
     * @return file
     */
    public function download($name) {
        return Storage::download($name);
    }
}
