<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use Log;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

use App\Models\UserFiles;

use App\Http\Traits\ResponseTemplate;

class UserFileContoller extends Controller
{
    use ResponseTemplate;

    private $targetModel;

    public function __construct () {
        $this->targetModel = new UserFiles;
    }

    public function index () {
        return $this->responseTemplate(null, false, [__('users_files.object_error')]);
    }

    public function store (Request $request) {
        // Validate the file
        $validator = Validator::make($request->all(), $this->getValidationRules());
        
        if ($validator->fails()) {
            return $this->responseTemplate(null, false, $validator->errors());
        }

        // Get file extension (e.g., 'pdf', 'jpg')
        $extension = $request->file('file')->getClientOriginalExtension();
        
        // Get file size in kilobytes
        $sizeInKb = $request->file('file')->getSize() / 1024;
        
        // Store the file
        $path = $request->file('file')->store('usersFiles', 'local');

        $data = [
            'path'          => $path,
            'extension'     => $extension,
            'size_in_kb'    => $sizeInKb,
            'title'         => $request->title,
            'user_id'       => $request->user_id,
        ];

        try {
            DB::beginTransaction();
            
            $file = $this->targetModel->create($data);
                
            DB::commit();
        } catch(Exception $exception) {
            DB::rollback();
            dd($exception);
            Log::error('UserFileContoller@store Exception', ['error' => $exception->getMessage()]);

            return $this->responseTemplate(null, false, [__('layouts.object_error')]);
        }
        
        return $this->responseTemplate($file, true, [__('layouts.file_stored')]);
    }
    
    public function show (Request $request, $id) {
        $file = $this->targetModel->find($id);

        if (!$file)
        abort(404, 'File not found.');
        // return $this->responseTemplate(null, false, __('layouts.file_not_found'));

        $path = storage_path('app/' . $file->path); // e.g. user-files/image1.jpg
        // dd($path, file_exists($path));
        // Check if file exists
        if (!file_exists($path)) {
            abort(404, 'File not found.');
        }

        // Return file inline (e.g. for images, PDFs)
        return response()->file($path);
    }

    public function update (Request $request, $id) {
        // Validate the file
        $validator = Validator::make($request->all(), $this->getValidationRules());
                
        if ($validator->fails()) {
            return $this->responseTemplate(null, false, $validator->errors());
        }
        
        $file = $this->targetModel->find($id);
        
        if (!$file) {
            return $this->responseTemplate(null, false, __('layouts.file_not_found'));
        }

        // Get file extension (e.g., 'pdf', 'jpg')
        $extension = $file->getClientOriginalExtension();
        
        // Get file size in kilobytes
        $sizeInKb = $file->getSize() / 1024;
        
        // Store the file
        $path = $request->file('file')->store('studentsFiles', 'public');

        $data = [
            'title'         => $request->title,
            'path'          => $path,
            'extension'     => $extension,
            'size_in_kb'    => $sizeInKb,
        ];

        try {
            DB::beginTransaction();
            
            $old_file = $file->path;

            $file = $this->targetModel->update($data);
            
            $this->deleteFile($old_file);
            
            DB::commit();
        } catch(Exception $exception) {
            DB::rollback();

            return $this->responseTemplate(null, false, [__('layouts.object_error')]);
        }
        
        return $this->responseTemplate($grade, true, [__('layouts.file_stored')]);
    }

    public function destroy ($id) {
        $file = $this->targetModel->find($id);

        if (!$file)
        return $this->responseTemplate(null, false, __('layouts.file_not_found'));
        
        $this->deleteFile($file->path);

        $file->delete();

        return $this->responseTemplate($file, true, __('layouts.file_deleted'));
    }

    //  HELPER METHODS
    private function getValidationRules (): array {
        return [
            'user_id'       => 'required|exists:users,id',
            'title'         => 'required|string|max:99',
            'file'          => 'required|file|mimes:jpg,jpeg,png,webp|max:10240', // max is in kilobytes (10240 = 10MB)
        ];
    }

    private function deleteFile ($filePath) {
        // Check if file exists
        if (Storage::disk('local')->exists($filePath)) {
            Storage::disk('local')->delete($filePath);
        }
    }

}
