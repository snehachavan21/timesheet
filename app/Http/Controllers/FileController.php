<?php

namespace App\Http\Controllers;

use App\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

class FileController extends Controller
{
    public function __construct()
    {
        $this->filePath = base_path() . env('FILE_UPLOAD_PATH', '/storage/files/');
    }

    public function uploadFile(Request $request)
    {
        try {
            try {
                DB::beginTransaction();
                $file = 'file';
                if ($request->has('filename')) {
                    $file = $request->input('filename');
                }

                $files = $request->file($file);

                if (!is_array($files)) {
                    $files[] = $request->file($file);
                }

                foreach ($files as $f) {
                    $extArr = explode("/", $f->getClientMimeType());
                    $mimeType = $f->getClientMimeType();
                    $fileSize = $f->getClientSize();
                    $originalFilename = $f->getClientOriginalName();
                    $type = 'local';

                    // check with the file extention

                    if ($extArr) {
                        $ext = $extArr[1];
                    }
                    if (!$ext) {
                        return false;
                    }

                    $fileName = uniqid() . "." . $ext;

                    if (!file_exists($this->filePath)) {
                        mkdir($this->filePath, 0777, true);
                    }

                    $f->move(
                        $this->filePath, $fileName
                    );

                    $filePath = $this->filePath . $fileName;

                    $file = File::create([
                        'file_name' => $fileName,
                        'mime_type' => $mimeType,
                        'file_size' => $fileSize,
                        'file_path' => $filePath,
                        'client_file_name' => $originalFilename,
                        'type' => $type,
                    ]);

                    $responseArr[] = $file;
                }
                DB::commit();
                return $responseArr;
            } catch (FileException $e) {
                // echo $e->getMessage();
                return response($e->getMessage(), 500);
            }
        } catch (Exception $e) {
            DB::rollBack();
            return response('There were some errors uploading files', 500);
        }
    }

    public function getDownload($id)
    {
        $file = File::find($id);
        $filePath = $this->filePath . $file->file_name;
        return response()->download($filePath);
    }
}
