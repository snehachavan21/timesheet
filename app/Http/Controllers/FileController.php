<?php

namespace App\Http\Controllers;

use App\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

class FileController extends Controller
{
    public function __construct()
    {
        $this->filePath = base_path() . env('FILE_UPLOAD_PATH', '/storage/files/');
        $this->s3Prefix = env('S3_URL');
    }

    /**
     * @param Request $request
     * @return array|\Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
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

                    $f->move($this->filePath, $fileName);

                    $filePath = $this->filePath . $fileName;

                    // if the designation is s3. Upload
                    if ($request->input('destination') == 's3') {
                        $uploadPath = $request->input('path') . '/' . $fileName;
                        $type = 's3';
                        $s3 = Storage::disk('s3');
                        $s3->put($uploadPath, file_get_contents($filePath), 'public');

                        unlink($filePath);
                        $filePath = $this->s3Prefix . $uploadPath;
                    }

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
        $s3 = Storage::disk('s3');

        if ('s3' == $file->type) {
            $fileUrl = env('S3_URL') . $file->file_path;
            return $fileUrl;
        }

        // $downloadUrl = $s3->getObjectUrl(env('S3_BUCKET'), 'data.txt', '+5 minutes', array(
        //     'ResponseContentDisposition' => 'attachment; filename="' . $file->fileName . '"',
        // ));

        // $filePath = $this->filePath . $file->file_name;
        // return response()->download($filePath);
    }
}
