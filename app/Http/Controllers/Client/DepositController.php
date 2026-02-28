<?php

namespace App\Http\Controllers\Client;

use Exception;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

use Yajra\DataTables\DataTables;

use App\Models\DepositRequest;
use App\Models\PaymentMethod;
use App\Services\DepositService;
use App\Http\Traits\ResponseTemplate;

class DepositController extends Controller
{
    use ResponseTemplate;

    public function __construct(
        protected DepositService $depositService
    ) {}

    public function index(Request $request)
    {
        $clientId = auth()->id();
        $wallet = auth()->user()->wallet;
        $balance = $wallet ? $wallet->balance : 0;

        $paymentMethods = PaymentMethod::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'account_details']);

        if ($request->ajax()) {
            $query = DepositRequest::where('client_id', $clientId)
                ->with(['paymentMethod:id,name'])
                ->orderByDesc('created_at');

            return DataTables::of($query)
                ->addColumn('formatted_date', function ($row) {
                    return $row->created_at->format('Y-m-d H:i');
                })
                ->addColumn('payment_method_name', function ($row) {
                    return $row->paymentMethod ? $row->paymentMethod->name : '-';
                })
                ->addColumn('amount_display', function ($row) {
                    return number_format($row->amount, 2);
                })
                ->addColumn('fee_display', function ($row) {
                    return number_format($row->fee_amount ?? 0, 2);
                })
                ->addColumn('total_display', function ($row) {
                    return number_format($row->total_to_pay ?? $row->amount, 2);
                })
                ->addColumn('status_badge', function ($row) {
                    $colors = [
                        'pending'  => 'warning',
                        'approved' => 'success',
                        'rejected' => 'danger',
                    ];
                    $color = $colors[$row->status] ?? 'secondary';
                    return '<span class="badge bg-' . $color . '">' . __('client.deposits.status_' . $row->status) . '</span>';
                })
                ->addColumn('has_proof', function ($row) {
                    return !empty($row->proof_image);
                })
                ->addColumn('can_cancel', function ($row) {
                    return $row->status === 'pending';
                })
                ->rawColumns(['status_badge'])
                ->make(true);
        }

        return view('clients.deposits.index', compact('balance', 'paymentMethods'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'amount'            => 'required|numeric|min:1',
            'payment_method_id' => 'required|exists:payment_methods,id',
            'proof_image'       => 'required|image|max:10240',
            'notes'             => 'nullable|string|max:500',
        ], [
            'amount.required'            => __('client.deposits.amount_required'),
            'amount.min'                 => __('client.deposits.amount_min'),
            'payment_method_id.required' => __('client.deposits.payment_method_required'),
            'proof_image.required'       => __('client.deposits.proof_required'),
            'proof_image.image'          => __('client.deposits.proof_image_type'),
            'proof_image.max'            => __('client.deposits.proof_max_size'),
        ]);

        if ($validator->fails()) {
            return $this->responseTemplate(null, false, $validator->errors());
        }

        $proofPath = null;
        if ($request->hasFile('proof_image') && $request->file('proof_image')->isValid()) {
            $proofPath = str_replace('public/', '', $request->file('proof_image')->store('public/media/deposit_proofs'));
        }

        if (!$proofPath) {
            return $this->responseTemplate(null, false, ['proof_image' => [__('client.deposits.proof_upload_failed')]]);
        }

        try {
            $data = [
                'client_id'         => auth()->id(),
                'amount'            => $request->amount,
                'payment_method_id' => $request->payment_method_id,
                'proof_image'       => $proofPath,
            ];

            $depositRequest = $this->depositService->createRequest($data);

            return $this->responseTemplate($depositRequest, true, __('client.deposits.request_submitted'));
        } catch (Exception $e) {
            Log::error('Client\DepositController@store', ['error' => $e->getMessage()]);
            return $this->responseTemplate(null, false, [__('client.deposits.error')]);
        }
    }

    public function show($id)
    {
        $clientId = auth()->id();

        $deposit = DepositRequest::where('client_id', $clientId)
            ->where('id', $id)
            ->with(['paymentMethod:id,name'])
            ->first();

        if (!$deposit) {
            return $this->responseTemplate(null, false, [__('client.deposits.not_found')]);
        }

        return $this->responseTemplate([
            'id'             => $deposit->id,
            'ref_number'     => $deposit->ref_number,
            'amount'         => number_format($deposit->amount, 2),
            'fee_amount'     => number_format($deposit->fee_amount ?? 0, 2),
            'total_to_pay'   => number_format($deposit->total_to_pay ?? $deposit->amount, 2),
            'payment_method' => $deposit->paymentMethod ? $deposit->paymentMethod->name : '-',
            'status'         => $deposit->status,
            'created_at'     => $deposit->created_at->format('Y-m-d H:i'),
            'action_date'    => $deposit->action_date ? $deposit->action_date->format('Y-m-d H:i') : null,
            'has_proof'      => !empty($deposit->proof_image),
        ], true, null);
    }

    public function viewProof($id)
    {
        $clientId = auth()->id();

        $deposit = DepositRequest::where('client_id', $clientId)
            ->where('id', $id)
            ->first();

        if (!$deposit || empty($deposit->proof_image)) {
            abort(404);
        }

        $path = Storage::disk('public')->path($deposit->proof_image);

        if (!file_exists($path)) {
            abort(404);
        }

        return response()->file($path);
    }

    public function calculateFee(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:0',
        ]);

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

    public function cancel($id)
    {
        $clientId = auth()->id();

        $deposit = DepositRequest::where('client_id', $clientId)
            ->where('id', $id)
            ->first();

        if (!$deposit) {
            return $this->responseTemplate(null, false, [__('client.deposits.not_found')]);
        }

        if ($deposit->status !== 'pending') {
            return $this->responseTemplate(null, false, [__('client.deposits.cannot_cancel')]);
        }

        $deposit->status = 'cancelled';
        $deposit->save();

        return $this->responseTemplate(null, true, __('client.deposits.cancelled'));
    }
}
