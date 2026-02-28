<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\DB;

use App\Models\DepositRequest;
use App\Models\FinancialSetting;
use App\Models\Wallet;

class DepositService
{
    public function __construct(
        protected WalletService $walletService,
        protected NotificationService $notificationService
    ) {}

    /**
     * Calculate fee and total for a given amount using current financial settings.
     */
    public function calculateFeeAndTotal(float $amount): array
    {
        $setting = FinancialSetting::getActive();
        return $setting->calculateFeeAndTotal($amount);
    }

    /**
     * Generate unique ref_number in format REQ-YYYYMM-XXX.
     */
    public function generateRefNumber(): string
    {
        $prefix = 'REQ-' . date('Ym') . '-';
        $last = DepositRequest::where('ref_number', 'like', $prefix . '%')
            ->orderBy('id', 'desc')
            ->value('ref_number');
        $seq = $last ? (int) substr($last, strlen($prefix)) + 1 : 1;
        return $prefix . str_pad((string) $seq, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Create a deposit request. Calculates fee_amount and total_to_pay from financial settings.
     */
    public function createRequest(array $data): DepositRequest
    {
        $amount = (float) $data['amount'];
        $calculated = $this->calculateFeeAndTotal($amount);

        $data['ref_number'] = $this->generateRefNumber();
        $data['fee_amount'] = $calculated['fee_amount'];
        $data['total_to_pay'] = $calculated['total_to_pay'];
        $data['status'] = 'pending';
        $data['created_by'] = auth()->id();

        return DepositRequest::create($data);
    }

    /**
     * Approve or reject a deposit request. Updates status, approved_by, reviewed_by, and action_date.
     */
    public function setStatus(int $requestId, string $status): DepositRequest
    {
        $request = DepositRequest::with('client')->findOrFail($requestId);
        if (!in_array($status, ['approved', 'rejected'])) {
            throw new Exception(__('deposit_requests.invalid_status'));
        }
        $userId = auth()->id();
        $request->status = $status;
        $request->approved_by = $userId;
        $request->reviewed_by = $userId;
        $request->action_date = now();
        $request->save();

        if ($status === 'approved') {
            $this->notificationService->notifyDepositApproved($request);
        } elseif ($status === 'rejected') {
            $this->notificationService->notifyDepositRejected($request);
        }

        return $request;
    }

    /**
     * Generate balance: add amount to client wallet and link transaction.
     * Only for approved requests without wallet_transaction_id.
     */
    public function generateBalance(int $requestId): DepositRequest
    {
        $request = DepositRequest::with('client.wallet')->findOrFail($requestId);

        if ($request->status !== 'approved') {
            throw new Exception(__('deposit_requests.must_be_approved'));
        }
        if ($request->wallet_transaction_id) {
            throw new Exception(__('deposit_requests.already_generated'));
        }

        $wallet = $request->client->wallet;
        if (!$wallet) {
            throw new Exception(__('deposit_requests.client_has_no_wallet'));
        }

        DB::beginTransaction();
        try {
            $transaction = $this->walletService->deposit(
                $wallet->id,
                (float) $request->amount,
                'Deposit request #' . ($request->ref_number ?: $request->id)
            );
            $request->wallet_transaction_id = $transaction->id;
            $request->processed_by = auth()->id();
            $request->save();
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }

        return $request->fresh();
    }

    /**
     * Analytics: total net deposits (amount added to wallets).
     */
    public function totalNetDeposits()
    {
        return DepositRequest::whereNotNull('wallet_transaction_id')->sum('amount');
    }

    /**
     * Analytics: total fees collected.
     */
    public function totalFeesCollected()
    {
        return DepositRequest::whereNotNull('wallet_transaction_id')->sum('fee_amount');
    }

    /**
     * Analytics: totals per payment account.
     */
    public function totalsPerPaymentMethod()
    {
        return DepositRequest::whereNotNull('wallet_transaction_id')
            ->selectRaw('payment_method_id, SUM(amount) as total_amount, SUM(fee_amount) as total_fees, SUM(total_to_pay) as total_collected')
            ->groupBy('payment_method_id')
            ->with('paymentMethod:id,name')
            ->get();
    }
}
