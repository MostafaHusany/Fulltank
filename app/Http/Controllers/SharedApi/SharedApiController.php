<?php

namespace App\Http\Controllers\SharedApi;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use Log;
use Exception;
use LaravelLocalization;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\StreamedResponse;

use App\Models\User;
use App\Models\MediaFile;
use App\Models\SchoolClass;

use App\Http\Traits\ResponseTemplate;

class SharedApiController extends Controller
{
    
    use ResponseTemplate;

    public function __construct () {
        $this->student     = new User;
        $this->mediaFile   = new MediaFile;
        $this->schoolClass = new SchoolClass;
    }

    public function mediaFiles (Request $request) {

        // $is_ar = LaravelLocalization::getCurrentLocale() == 'ar'; 

        $media = $this->mediaFile->query()
        ->with(['levels', 'subjects'])
        ->adminFilter()
        ->orderBy('id', 'desc')
        ->get();

        
        return $this->responseTemplate($media, true);
        
    }

    public function schoolClasses (Request $request) {

        $schoolClasses = $this->schoolClass->query()
        ->with(['grade', 'level'])
        ->adminFilter()
        ->orderBy('id', 'desc')
        ->get();

        
        return $this->responseTemplate($schoolClasses, true);
        
    }

    public function students (Request $request) {

        $students = $this->student->query()
        ->with(['student' => fn ($q) => $q->with(['grade', 'level'])])
        ->where('category', 'student')
        ->studentFilter()
        ->orderBy('id', 'desc')
        ->get();

        
        return $this->responseTemplate($students, true);
        
    }
    
}
