<?php

namespace App\Http\Controllers;

use App\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

use Symfony\Component\HttpFoundation\File\Exception\FileException;

class FileController extends Controller
{
    public function __construct()
    {
        $this->filePath = base_path()  . env('FILE_UPLOAD_PATH', '/storage/files/');
    }

    public function uploadFile(Request $request)
    {
        try {
            try{
                DB::beginTransaction();
                $file = 'file';
                if($request->has('filename'))
                    $file = $request->input('filename');

                $extArr = explode("/", $request->file($file)->getClientMimeType());
                $mimeType = $request->file($file)->getClientMimeType();
                $fileSize = $request->file($file)->getClientSize();
                $originalFilename = $request->file($file)->getClientOriginalName();
                $type = 'local';

                // check with the file extention

                if ($extArr) {
                    $ext = $extArr[1];
                }
                if(!$ext) {
                    return false;
                }

                $fileName = uniqid() . "." . $ext;

                if (!file_exists($this->filePath)) {
                    mkdir($this->filePath, 0777, true);
                }

                $request->file($file)->move(
                    $this->filePath, $fileName
                );

                $filePath = $this->filePath . $fileName;


                $file = File::create([
                    'file_name' => $fileName,
                    'mime_type' => $mimeType,
                    'file_size' => $fileSize,
                    'file_path' =>  $filePath,
                    'client_file_name' => $originalFilename,
                    'type' => $type,
                ]);

                DB::commit();

                return $file;

            } catch (FileException $e){
//                echo $e->getMessage();
                return response($e->getMessage(), 500);
            }
        } catch (Exception $e) {
            DB::rollBack();
            return response('There were some errors uploading files', 500);
        }
    }

    public function getDownload($id){
        $file = File::find($id);
        $filePath = $this->filePath. $file->file_name;
        return response()->download($filePath);
    }
}
