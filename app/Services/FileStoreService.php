<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;

class FileStoreService extends Service {

    private $file_dir = '/uploads';

    public function upload($file, $file_name, $disk = null) {
        $disk = $disk ?? ENV('FILESYSTEM_DRIVER');
        $path = Storage::disk($disk)->putFile($this->file_dir, $file);
        return [
            'size' => Storage::disk($disk)->size($path),
            'name' => $file_name,
            'url' => Storage::disk($disk)->url($path),
            'stored_name' => basename($path),
            'path' => Storage::disk($disk)->path($path),
            'relative_path' => $path
        ];
    }

    public function uploadAs($file, $file_name, $disk = null) {
        $disk = $disk ?? ENV('FILESYSTEM_DRIVER');
        $path = Storage::disk($disk)->putFileAs($this->file_dir, $file, $file_name);
        return [
            'size' => Storage::disk($disk)->size($path),
            'name' => $file_name,
            'url' => Storage::disk($disk)->url($path),
            'stored_name' => basename($path),
            'path' => Storage::disk($disk)->path($path),
            'relative_path' => $path
        ];
    }

    public function uploadContents($file, $file_name, $disk = null) {
        $disk = $disk ?? ENV('FILESYSTEM_DRIVER');
        $path = $this->file_dir . '/' . $file_name;
        Storage::disk($disk)->put($path, $file);

        return [
            'size' => Storage::disk($disk)->size($path),
            'name' => $file_name,
            'url' => Storage::disk($disk)->url($path),
            'stored_name' => basename($path),
            'path' => Storage::disk($disk)->path($path),
            'relative_path' => $path
        ];
    }

    public function download($file, $disk = null) {
        $disk = $disk ?? ENV('FILESYSTEM_DRIVER');

        return Storage::disk($disk)->download($file);
    }

    public function delete($file, $disk = null) {
        $disk = $disk ?? ENV('FILESYSTEM_DRIVER');

        return Storage::disk($disk)->delete($file);
    }
}