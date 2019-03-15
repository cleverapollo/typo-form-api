<?php

namespace App\Http\Controllers;

use Exception;
// use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use App\Services\FileStoreService;

class FileController extends Controller
{
    private $fileStoreService;

    public function __construct() {
        $this->fileStoreService = new FileStoreService;
    }
    
	/**
	 * Store File
	 *
	 * @param  Request $request
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function store(Request $request)
	{
        $file = $this->fileStoreService
            ->upload($request->file('file'), $request->file('file')->getClientOriginalName());
            
        return $this->jsonResponse(['file' => $file]);
    }
    
    /**
     * Destroy File
     *
     * @param Request $request
	 * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Request $request) {
        $result = $this->fileStoreService
            ->delete($request->input('name'));

        return $this->jsonResponse(['result', $result]);
    }

    /**
     * Download File
     *
     * @param string $name
     * @return file
     */
    public function download($name) {
        return $this->fileStoreService->download($name);
    }
}
