<?php

namespace App\Http\Controllers;

use LaravelLocalization;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

use App\Models\Feedback;

use App\Http\Traits\ResponseTemplate;

class FeedbackController extends Controller
{
    use ResponseTemplate;

    public function __construct() {
        LaravelLocalization::setLocale('ar');
        
        $this->targetModel = new Feedback;
    }

    public function index (Request $request) {
        
        LaravelLocalization::setLocale('ar');
        
        return view('feedback.index');
    }

    public function store (Request $request) {
        $validator = Validator::make($request->all(), [
            'name'                      => 'nullable|max:255|min:3',
            'phone'                     => 'nullable|regex:/^01[0-2,5]{1}[0-9]{8}$/',
            'email'                     => 'nullable|email|max:255|min:3',
            'gender'                    => 'required|in:male,female',
            'is_disabled'               => 'required|in:1,0',
            'details'                   => 'required|min:10|max:9999',

            'age'                       => 'required|numeric|min:10|max:80',
            'rating'                    => 'required|numeric',
            'role'                      => 'required|in:trainer,participant,facilitator',
            // 'priority_level'            => 'required|in:low,medium,heigh,immediate',
            
            'gove_id'                   => 'required|exists:districts,id', 
            'dawwie_activitie_id'       => 'required|exists:dawwie_activities,id',
            'feedback_type_id'          => 'required|exists:feedback_types,id',
            'aspect_id'                 => 'required|exists:feedback_types,id',
            'trainer_id'                => 'required|exists:users,id'
        ]);

        if ($validator->fails()) {
            return $this->responseTemplate(null, false, $validator->errors());
        }
        
        $data = $request->only($this->targetModel->getFillable());
        
        try {
            DB::beginTransaction();
            
            $feedback = $this->targetModel->create($data);
                
            DB::commit();
        } catch(Exception $exception) {
            DB::rollback();

            return $this->responseTemplate(null, false, [__('feedbacks.object_error')]);
        }

        return $this->responseTemplate($feedback, true, [__('feedbacks.object_created')]);
    }
}
