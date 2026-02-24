<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use Log;
use Exception;
use LaravelLocalization;

use ZipArchive;
use Illuminate\Support\Str;
use Yajra\Datatables\Datatables;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\StreamedResponse;

use App\Models\MediaFile;

use App\Http\Traits\ResponseTemplate;

class MediaFileController extends Controller
{
    use ResponseTemplate;

    public function __construct () {
        $this->targetModel = new MediaFile;
    }

    public function index (Request $request) {

        $is_ar = LaravelLocalization::getCurrentLocale() == 'ar'; 

        $permissions = auth()->user()->category == 'admin' 
            ? 'admin' 
            : $this->getPermissions(['mediaFile_add', 'mediaFile_edit', 'mediaFile_delete', 'mediaFile_show']);
        
        if ($request->ajax()) {
            $model = $this->targetModel->query()
            ->withCount(['learningLists as learning_lists_count'])
            ->with(['user', 'grades', 'levels', 'subjects'])
            ->orderBy('id', 'desc')
            ->adminFilter();
            
            $datatable_model = Datatables::of($model)
            ->addColumn('checkbox_selector', function ($row_object) {
                return view('layouts.admin.incs._checkbox_selector', compact('row_object'));
            })
            ->addColumn('user', function ($row_object) {
                return $row_object->user ? $row_object->user->name : '---';
            })
            ->addColumn('grades', function ($row_object) use ($is_ar) {
                return view('admin.media_files.incs._grades', compact('row_object', 'is_ar'));
            })
            ->addColumn('levels', function ($row_object) use ($is_ar) {
                return view('admin.media_files.incs._levels', compact('row_object', 'is_ar'));
            })
            ->addColumn('subjects', function ($row_object) use ($is_ar) {
                return view('admin.media_files.incs._subjects', compact('row_object', 'is_ar'));
            })
            ->addColumn('learning_lists_btn', function ($row_object) {
                return view('admin.media_files.incs._learning_lists_btn', compact('row_object'));
            })
            ->addColumn('activation', function ($row_object) use ($permissions) {
                return $row_object->type == 'scorm' && $row_object->scorm_status != 'extracted'
                    ? view('admin.media_files.incs._scorm', compact('row_object', 'permissions'))
                    : view('admin.media_files.incs._active', compact('row_object', 'permissions'));
            })
            ->addColumn('actions', function ($row_object) use ($permissions, $is_ar) {
                return view('admin.media_files.incs._actions', compact('row_object', 'permissions', 'is_ar'));
            });

            return $datatable_model->make(true);
        }

        return view('admin.media_files.index', compact('permissions'));
    }

    public function store (Request $request) {
        $validator = Validator::make($request->all(), $this->getValidationRules($request));
        
        if ($validator->fails()) {
            return $this->responseTemplate(null, false, $validator->errors());
        }
        
        $data = $this->formatRequest($request);

        try {
            DB::beginTransaction();
            
            $media = $this->targetModel->create($data);
            
            if ($request->grades)
            $media->grades()->sync(is_array($request->grades) ? $request->grades : explode(',', $request->grades));
        
            if ($request->levels)
            $media->levels()->sync(is_array($request->levels) ? $request->levels : explode(',', $request->levels));
        
            if ($request->subjects)
            $media->subjects()->sync(is_array($request->subjects) ? $request->subjects : explode(',', $request->subjects));
            
            DB::commit();
        } catch(Exception $exception) {
            DB::rollback();
            
            Log::error('MediaFileController@store Exception', ['error' => $exception->getMessage()]);
            
            return $this->responseTemplate(null, false, [__('media_files.object_error')]);
        }

        return $this->responseTemplate($media, true, [__('media_files.object_created')]);
    }

    public function show ($id) {

        $media = $this->targetModel->with(['grades', 'levels', 'subjects'])->find($id);
        
        if (!$media) {
            return $this->responseTemplate(null, false, __('media_files.object_not_found'));
        }

        return $this->responseTemplate($media, true);
    }

    public function update (Request $request, $id) {
        $media = $this->targetModel->find($id);
        
        if (!$media) {
            return $this->responseTemplate(null, false, __('mediaes.object_not_found'));
        }

        if (isset($request->extract_scorm)) {
            return $this->extractFile($request, $media);
        }

        return isset($request->activate_object) 
            ? $this->activateObj($request, $media) 
            : $this->updateObj($request, $media);
    }

    public function destroy (Request $request, $id) {
        $media = $this->targetModel->find($id);

        if ($media) {

            if ($media->scorm_status != 'extracted' && Storage::disk('local')->exists($media->path)) {
                Storage::disk('local')->delete($media->path);
            } else {
                $dir = dirname(public_path($media->path));
                
                if (is_dir($dir))
                shell_exec("rm -rf " . $dir); 
            }

            $media->delete();
        }

        return $this->responseTemplate($media, true, __('media_files.object_deleted'));
    }

    //  HELPER METHODS
    private function getValidationRules(Request $request, $id = null): array {
        return [
            'title'         => 'required|max:150|min:3',
            'description'   => 'nullable|max:99999',
            'type'          => 'required|in:document,image,video,audio,scorm',
            'grades'        => 'required', 
            'levels'        => 'required',
            'subjects'      => 'required',
            'file'          => ($id ? 'nullable' : 'required') . '|file|max:101200' 
                . ($request->input('type') == 'image'      ? '|mimes:jpeg,jpg,png,gif,webp' : '')
                . ($request->input('type') == 'video'      ? '|mimes:mp4,avi,mkv,mov,webm' : '')
                . ($request->input('type') == 'audio'      ? '|mimes:mp3,wav,ogg,m4a' : '')
                . ($request->input('type') == 'document'   ? '|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,txt' : '') 
                . ($request->input('type') == 'scorm'      ? '|mimes:zip,rar' : ''),
        ];
    }

    private function formatRequest (Request $request, MediaFile $media = null) {
        $data = $request->only($this->targetModel->getFillable());
        $data['user_id'] = auth()->user()->id;

        if ($request->file('file')) {

            // Store the file
            $data['path'] = $request->file('file')->store('mediaFiles', 'local');
            
            // Get file extension (e.g., 'pdf', 'jpg')
            $data['extension'] = $request->file('file')->getClientOriginalExtension();
            
            // Get file size in kilobytes
            $data['size_in_kb'] = $request->file('file')->getSize() / 1024;

            // set scorm status to unextracted in all casses of new file upload
            $data['scorm_status'] = 'unextracted';
            
            // delete old file
            if (isset($media)) {
                if ($media->scorm_status != 'extracted' && Storage::disk('local')->exists($media->path))
                Storage::disk('local')->delete($media->path);
                else {
                    $dir = dirname(public_path($media->path));
                    
                    if (is_dir($dir))
                    shell_exec("rm -rf " . $dir); 
                }
            }
         
        }

        return $data;
    }
    
    private function updateObj (Request $request, MediaFile $media) {
        $validator = Validator::make($request->all(), $this->getValidationRules($request, $media->id));

        if ($validator->fails()) {
            return $this->responseTemplate(null, false, $validator->errors());
        }
        
        $data = $this->formatRequest($request, $media);
        
        try {
            DB::beginTransaction();
            
            $media->update($data);
            
            if ($request->grades)
            $media->grades()->sync(is_array($request->grades) ? $request->grades : explode(',', $request->grades));
        
            if ($request->levels)
            $media->levels()->sync(is_array($request->levels) ? $request->levels : explode(',', $request->levels));
        
            if ($request->subjects)
            $media->subjects()->sync(is_array($request->subjects) ? $request->subjects : explode(',', $request->subjects));
            
            DB::commit();
        } catch(Exception $exception) {
            DB::rollback();
            
            Log::error('MediaFileController@update Exception', ['error' => $exception->getMessage()]);

            return $this->responseTemplate(null, false, [__('media_files.object_error')]);
        }

        return $this->responseTemplate($media, true, [__('media_files.object_updated')]);
    }

    private function activateObj (Request $request, MediaFile $media) {

        try {
            DB::beginTransaction();

            $media->is_active = !$media->is_active;
            $media->save();

            DB::commit();
        } catch(Exception $exception) {
            DB::rollback();

            return $this->responseTemplate(null, false, [__('media_files.object_error')]);
        }

        return $this->responseTemplate($media, true, __('media_files.object_updated'));
    }

    private function extractFile (Request $request, MediaFile $media) {

        try {
            DB::beginTransaction();

            $zipPath = storage_path('app/' . $media->path);
            $extractPath = storage_path('app/public/media/extracted_scorm/' . $media->id);
            
            $zip = new ZipArchive;
            
            if ($zip->open($zipPath) === true) {
                $zip->extractTo($extractPath);
                $zip->close();

                unlink($zipPath);

                $indexFiles = ['story.html', 'index.html'];

                $indexPath = null;

                foreach ($indexFiles as $file) {
                    if (file_exists("{$extractPath}/{$file}")) {
                        $indexPath = "{$extractPath}/{$file}";
                        break;
                    }
                }
                
                if ($indexPath) {
                    $media->path         = 'media/extracted_scorm/' . $media->id . '/' . $file;
                    $media->scorm_status = 'extracted';
                    $media->save();
                } else {
                    $media->scorm_status = 'corrupted';
                    $media->save();
                }

            }

            DB::commit();
        } catch(Exception $exception) {
            DB::rollback();
            
            $media->scorm_status = 'failed';
            $media->save();

            return $this->responseTemplate(null, false, [__('media_files.object_error')]);
        }

        return $this->responseTemplate($media, true, __('media_files.object_updated'));
    }

    // extra methods for handling media
    public function view ($id) {
        $media = $this->targetModel->find($id);
        
        if (!$media) {
            abort(404, 'File not found');
        }

        if (in_array($media->extension, ['mp4', 'avi', 'mkv', 'mov', 'webm', 'mp3', 'wav', 'ogg', 'm4a'])) {
            return $this->streamVideo($media->path);
        } else {
            return $this->viewFile($media->path);
        }
    }

    private function viewFile($path) {
        if (!Storage::exists($path)) {
            abort(404, 'File not found');
        }

        $mime = Storage::mimeType($path);
        $filename = basename($path);

        return new StreamedResponse(function () use ($path) {
            echo Storage::get($path);
        }, 200, [
            "Content-Type" => $mime,
            "Content-Disposition" => "inline; filename=\"{$filename}\""
        ]);
    }

    private function downloadFile($path) {
        if (!Storage::exists($path)) {
            abort(404, 'File not found');
        }

        $mime = Storage::mimeType($path);
        $filename = basename($path);

        return new StreamedResponse(function () use ($path) {
            echo Storage::get($path);
        }, 200, [
            "Content-Type" => $mime,
            "Content-Disposition" => "attachment; filename=\"{$filename}\""
        ]);
    }

    private function streamVideo($path) {
        if (!Storage::exists($path)) {
            abort(404, 'Video not found');
        }

        $size = Storage::size($path);
        $mime = Storage::mimeType($path);
        $file = Storage::path($path);

        $start = 0;
        $end = $size - 1;

        if (request()->headers->has('Range')) {
            $range = request()->header('Range');
            [$start, $end] = explode('-', str_replace('bytes=', '', $range));
            $start = intval($start);
            $end = $end === '' ? $size - 1 : intval($end);
        }

        $length = $end - $start + 1;

        $headers = [
            'Content-Type' => $mime,
            'Content-Length' => $length,
            'Content-Range' => "bytes $start-$end/$size",
            'Accept-Ranges' => 'bytes',
        ];

        return new StreamedResponse(function () use ($file, $start, $length) {
            $handle = fopen($file, 'rb');
            fseek($handle, $start);
            echo fread($handle, $length);
            fclose($handle);
        }, (request()->headers->has('Range') ? 206 : 200), $headers);
    }

}
