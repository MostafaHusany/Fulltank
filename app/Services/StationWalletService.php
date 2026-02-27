<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\DB;

use App\Models\Wallet;
use App\Models\Transaction;

class StationWalletService
{
    /**
     * Ensure wallet is active before any credit operation.
     */
    public function ensureWalletActive(Wallet $wallet): void
    {
        if (!$wallet->is_active) {
            throw new Exception(__('station_wallets.wallet_inactive'));
        }
    }

    /**
     * Toggle wallet is_active status.
     */
    public function toggleStatus(int $walletId): Wallet
    {
        $wallet = Wallet::findOrFail($walletId);
        $wallet->is_active = !$wallet->is_active;
        $wallet->save();
        return $wallet;
    }

    /**
     * Get transaction history for a wallet.
     */
    public function getTransactions(int $walletId): array
    {
        $wallet = Wallet::with('user:id,name')->findOrFail($walletId);

        $transactions = $wallet->transactions()
            ->with('creator:id,name')
            ->orderBy('id', 'desc')
            ->get();

        return $transactions->map(function ($t) {
            return [
                'id'             => $t->id,
                'amount'         => (float) $t->amount,
                'type'           => $t->type,
                'notes'          => $t->notes,
                'before_balance' => (float) $t->before_balance,
                'after_balance'  => (float) $t->after_balance,
                'created_at'     => $t->created_at?->format('Y-m-d H:i'),
                'performer_name' => $t->creator ? e($t->creator->name) : '---',
            ];
        })->toArray();
    }
}
