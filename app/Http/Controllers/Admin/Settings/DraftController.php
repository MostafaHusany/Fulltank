<?php

namespace App\Http\Controllers\Admin\Settings;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;

use App\Models\Draft;

use App\Http\Traits\ResponseTemplate;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class DraftController extends Controller
{
    use ResponseTemplate;

    private $targetModel;

    public function __construct() {
        $this->targetModel = new Draft;
    }

    public function index (Request $request) {
        $drafts = $this->targetModel->query()
        ->where('user_id', auth()->user()->id)
        ->where('section_flag', $request->section_flag)
        ->orderBy('id', 'desc')
        ->get();

        return $this->responseTemplate($drafts, true);

    }

    public function store (Request $request) {
        // if has files, store the files
        // use the media model to record the saved image
        
        $files_paths = $this->uploadFile($request);
        
        $data = $request->all();
        $data['files_paths'] = $files_paths;
        
        $draft = $this->targetModel->create([
            'title'        => $request->title,
            'user_id'      => auth()->user()->id,
            'section_flag' => $request->section_flag,
            'meta'         => json_encode($data)
        ]);

        return $this->responseTemplate($draft, true, __('drafts.object_created'));
    }

    public function show ($id) {
        $draft = $this->targetModel->where('user_id', auth()->user()->id)->find($id);

        if (!isset($draft))
        return $this->responseTemplate(null, false, __('drafts.object_not_found'));

        return $this->responseTemplate($draft, true);
    }

    public function destroy ($id) {
        $draft = $this->targetModel->where('user_id', auth()->user()->id)->find($id);

        if (!isset($draft))
        return $this->responseTemplate(null, false, __('drafts.object_not_found'));

        $draft->delete();

        return $this->responseTemplate($draft, true);
    }

    public function dataAjax(Request $request) {
    	
        $model = $this->targetModel->query()
        ->where('user_id', auth()->user()->id);

        if (isset($request->section_flag))
        $model->where('section_flag', $request->section_flag);

        if (isset($request->q))
        $model->where('title', 'like', "%$request->q%");

        $data = $model->get();

        return response()->json($data);
    }

    // START HELPERS
    private function uploadFile (Request $request) {
        $files_paths  = []; 
        $files_fields = explode(',', $request->files_fields);

        forEach($files_fields as $field) {
            if ($request->file($field) != null)
            $files_paths[$field] = $request->file($field)->store('media/drafts', 'public');
        }

        return $files_paths;
    }

}
