<?php

namespace App\Http\Controllers\SharedApi;

use Illuminate\Http\Request;
use App\Services\GoogleDriveService;
use App\Http\Controllers\Controller;

use App\Http\Traits\ResponseTemplate;

class GoogleDriveController extends Controller
{
    use ResponseTemplate;

    private $drive;

    public function __construct(GoogleDriveService $drive)
    {
        $this->drive = $drive;
    }

    public function listFiles(Request $request)
    {
        $folderId = isset($request->folderId) ? $request->folderId : '1hKExbHLPp1fhYJGIKSTp3GuO5HOK12Li';
        
        $files = $this->drive->listFilesInFolder($folderId);
        
        // return response()->json($files);
        return $this->responseTemplate($files, true, null);
    }

    public function getFileLink($fileId)
    {
        $file = $this->drive->getFile($fileId);
        return response()->json([
            'id' => $file->id,
            'name' => $file->name,
            'mimeType' => $file->mimeType,
            'viewLink' => $file->webViewLink,
            'downloadLink' => $file->webContentLink,
        ]);
    }
}
