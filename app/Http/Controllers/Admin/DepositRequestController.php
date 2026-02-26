<?php

namespace App\Http\Controllers\Admin;

use Exception;
use Log;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use Yajra\Datatables\Datatables;
use Illuminate\Support\Facades\Validator;

use Illuminate\Support\Facades\Storage;

use App\Models\DepositRequest;
use App\Models\PaymentMethod;

use App\Services\DepositService;

use App\Http\Traits\ResponseTemplate;

class DepositRequestController extends Controller
{
    use ResponseTemplate;

    public function __construct(
        protected DepositService $depositService
    ) {}

    public function index(Request $request)
    {
        $permissions = auth()->user()->category == 'admin'
            ? 'admin'
            : $this->getPermissions(['depositRequests_add', 'depositRequests_edit', 'depositRequests_delete', 'depositRequests_show']);

        if ($request->ajax()) {
            $filters = [];
            try {
                $filters = $request->validate([
                    'ref_number'  => 'nullable|string|max:50',
                    'client_id'   => 'nullable|integer|exists:users,id',
                    'status'      => 'nullable|in:pending,approved,rejected',
                    'start_date'  => 'nullable|date',
                    'end_date'    => 'nullable|date|after_or_equal:start_date',
                ]);
            } catch (\Illuminate\Validation\ValidationException $e) {
                $filters = [];
            }

            $model = DepositRequest::query()
                ->with(['client:id,name,company_name', 'paymentMethod:id,name,account_details', 'reviewer:id,name', 'processor:id,name'])
                ->when(!empty($filters['ref_number']), fn($q) => $q->where('ref_number', 'like', '%' . $filters['ref_number'] . '%'))
                ->when(!empty($filters['client_id']), fn($q) => $q->where('client_id', $filters['client_id']))
                ->when(!empty($filters['status']), fn($q) => $q->where('status', $filters['status']))
                ->when(!empty($filters['start_date']), fn($q) => $q->whereDate('created_at', '>=', $filters['start_date']))
                ->when(!empty($filters['end_date']), fn($q) => $q->whereDate('created_at', '<=', $filters['end_date']))
                ->orderBy('id', 'desc');

            $datatable = Datatables::of($model)
            ->addColumn('ref_number', function ($row) {
                return view('admin.deposit_requests.incs._ref_number', compact('row'))->render();
            })
            ->addColumn('request_date', function ($row) {
                return $row->created_at ? $row->created_at->format('Y-m-d H:i') : '---';
            })
            ->addColumn('client_name', function ($row) {
                return $row->client ? e($row->client->company_name ?: $row->client->name) : '---';
            })
            ->addColumn('payment_method_name', function ($row) {
                return $row->paymentMethod ? e($row->paymentMethod->name) : '---';
            })
            ->addColumn('reviewer_name', function ($row) {
                return view('admin.deposit_requests.incs._reviewer_name', compact('row'))->render();
            })
            ->addColumn('processor_name', function ($row) {
                return view('admin.deposit_requests.incs._processor_name', compact('row'))->render();
            })
            ->addColumn('proof_thumb', function ($row) {
                return view('admin.deposit_requests.incs._proof_thumb', compact('row'))->render();
            })
            ->addColumn('status_badge', function ($row) {
                return view('admin.deposit_requests.incs._status_badge', compact('row'))->render();
            })
            ->addColumn('actions', function ($row) use ($permissions) {
                return view('admin.deposit_requests.incs._actions', compact('row', 'permissions'))->render();
            })
            ->rawColumns(['ref_number', 'proof_thumb', 'status_badge', 'actions']);

            return $datatable->make(true);
        }

        return view('admin.deposit_requests.index', compact('permissions'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'client_id'        => 'required|exists:users,id',
            'amount'           => 'required|numeric|min:0.01',
            'payment_method_id' => 'required|exists:payment_methods,id',
            'proof_image'      => 'required|image|max:10240',
        ]);

        if ($validator->fails()) {
            return $this->responseTemplate(null, false, $validator->errors());
        }

        $proofPath = $request->hasFile('proof_image') && $request->file('proof_image')->isValid()
            ? str_replace('public/', '', $request->file('proof_image')->store('public/media/deposit_proofs'))
            : null;

        if (!$proofPath) {
            return $this->responseTemplate(null, false, ['proof_image' => [__('deposit_requests.proof_required')]]);
        }

        try {
            $data = $request->only(['client_id', 'amount', 'payment_method_id']);
            $data['proof_image'] = $proofPath;
            $depositRequest = $this->depositService->createRequest($data);
            return $this->responseTemplate($depositRequest, true, __('deposit_requests.object_created'));
        } catch (Exception $e) {
            Log::error('DepositRequestController@store', ['error' => $e->getMessage()]);
            return $this->responseTemplate(null, false, [__('deposit_requests.object_error')]);
        }
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make(
            array_merge($request->all(), ['id' => $id]),
            [
                'id'     => 'required|exists:deposit_requests,id',
                'status' => 'required|in:approved,rejected',
            ]
        );

        if ($validator->fails()) {
            return $this->responseTemplate(null, false, $validator->errors());
        }

        try {
            $status = $request->status;
            $dr = $this->depositService->setStatus((int) $id, $status);
            $msg = $status === 'approved' ? __('deposit_requests.approved_msg') : __('deposit_requests.rejected_msg');
            
            return $this->responseTemplate($dr, true, $msg);
        } catch (Exception $e) {
            Log::error('DepositRequestController@update', ['error' => $e->getMessage()]);
           
            return $this->responseTemplate(null, false, [$e->getMessage()]);
        }
    }

    public function generateBalance(Request $request, $id)
    {
        $validator = Validator::make(['id' => $id], ['id' => 'required|exists:deposit_requests,id']);
        if ($validator->fails()) {
            return $this->responseTemplate(null, false, $validator->errors());
        }
        try {
            $dr = $this->depositService->generateBalance((int) $id);
            return $this->responseTemplate($dr, true, __('deposit_requests.balance_generated'));
        } catch (Exception $e) {
            Log::error('DepositRequestController@generateBalance', ['error' => $e->getMessage()]);
            return $this->responseTemplate(null, false, [$e->getMessage()]);
        }
    }

    public function calculateFee(Request $request)
    {
        $validator = Validator::make($request->all(), ['amount' => 'required|numeric|min:0']);
        if ($validator->fails()) {
            return $this->responseTemplate(null, false, $validator->errors());
        }
        try {
            $result = $this->depositService->calculateFeeAndTotal((float) $request->amount);
            return $this->responseTemplate($result, true, null);
        } catch (Exception $e) {
            return $this->responseTemplate(null, false, [$e->getMessage()]);
        }
    }

    public function viewProofImage(Request $request, $id)
    {
        $dr = DepositRequest::find($id);
        if (!$dr || !$dr->proof_image) {
            abort(404, __('deposit_requests.object_not_found'));
        }
        $path = Storage::disk('public')->path($dr->proof_image);
        if (!file_exists($path)) {
            abort(404, __('deposit_requests.object_not_found'));
        }
        return response()->file($path);
    }

    public function generatedRecord(Request $request, $id)
    {
        $dr = DepositRequest::with('walletTransaction')->find($id);
        if (!$dr || !$dr->wallet_transaction_id || !$dr->walletTransaction) {
            return $this->responseTemplate(null, false, [__('deposit_requests.object_not_found')]);
        }
        $tx = $dr->walletTransaction;
        return $this->responseTemplate([
            'amount'     => (float) $tx->amount,
            'notes'      => $tx->notes,
            'created_at' => $tx->created_at->format('Y-m-d H:i'),
        ], true, null);
    }

    public function analytics(Request $request)
    {
        try {
            $netDeposits = $this->depositService->totalNetDeposits();
            $totalFees = $this->depositService->totalFeesCollected();
            $perMethod = $this->depositService->totalsPerPaymentMethod();
            return $this->responseTemplate([
                'total_net_deposits'  => (float) $netDeposits,
                'total_fees_collected' => (float) $totalFees,
                'per_payment_method'   => $perMethod,
            ], true, null);
        } catch (Exception $e) {
            return $this->responseTemplate(null, false, [$e->getMessage()]);
        }
    }
}
