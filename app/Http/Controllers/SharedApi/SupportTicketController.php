<?php

namespace App\Http\Controllers\SharedApi;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use Log;
use Exception;
use LaravelLocalization;
use Yajra\Datatables\Datatables;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

use App\Models\User;
use App\Models\Semester;
use App\Models\SupportTicket;

use App\Http\Traits\ResponseTemplate;

class SupportTicketController extends Controller
{
    use responseTemplate;
    
    private $targetModel, $semester;

    public function __construct () {
        
        $this->targetModel  = new SupportTicket;
        
        $this->user         = new User;
        $this->semester     = Semester::query()->where('is_active', 1)->first();

    }

    public function index (Request $request) {
        if (!$this->semester)
            return $this->responseTemplate([], true, null);

        $support_tickets = $this->targetModel->query()
        ->with(['owner'])
        ->orderBy('id', 'desc')
        ->where('semester_id', $this->semester->id)
        ->sharedFilter()
        ->limit(20)
        ->get();

        return $this->responseTemplate($support_tickets, true, null);
    }

    public function show ($id) {

        $support_ticket = $this->targetModel->with(['owner'])->find($id);
        
        if (!$support_ticket) {
            return $this->responseTemplate(null, false, __('support_tickets.object_not_found'));
        }

        return $this->responseTemplate($support_ticket, true, null);
    }

    public function store (Request $request) {
        $validator = Validator::make($request->all(), $this->getValidationRules());

        if ($validator->fails()) {
            return $this->responseTemplate(null, false, $validator->errors());
        }

        $is_exists = $this->targetModel->query()
        ->where('user_id', auth()->user()->id)
        ->where('semester_id', $this->semester->id)
        ->whereIn('status', ['wating', 'open'])
        ->count();

        if ($is_exists > 2) {
            return $this->responseTemplate(null, false, [__('support_tickets.already_opened')]);
        }
        
        $data = $this->formatRequest($request);
        
        try {
            DB::beginTransaction();
            
            $support_ticket = $this->targetModel->create($data);
            
            DB::commit();
        } catch(Exception $exception) {
            DB::rollback();
            
            Log::error('SupportTicketController@store Exception', ['error' => $exception->getMessage()]);

            return $this->responseTemplate(null, false, [__('support_tickets.object_error')]);
        }

        return $this->responseTemplate($support_ticket, true, [__('support_tickets.object_created')]);
    }

    //  HELPER METHODS
    private function getValidationRules(): array {
        return [
            'title'         => 'required|string|max:255',
            'description'   => 'required|string',
            'type'          => 'required|in:meeting,technical,problem',
        ];
    }

    private function formatRequest (Request $request) {
        $data = $request->only($this->targetModel->getFillable());
        
        $owner = auth()->user();
        
        $data['semester_id'] = $this->semester ? $this->semester->id : null;
        $data['owner_name'] = $owner ? $owner->name : '';
        $data['owner_category'] = $owner ? $owner->category : '';
        $data['open_date'] = date('Y-m-d');

        return $data;
    }


}
