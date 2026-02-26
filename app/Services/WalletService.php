<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\DB;

use App\Models\Wallet;
use App\Models\Transaction;

class WalletService
{
    /**
     * Ensure wallet is active before any operation that deducts or transfers money.
     */
    public function ensureWalletActive(Wallet $wallet): void
    {
        if (!$wallet->is_active) {
            throw new Exception(__('wallets.wallet_inactive'));
        }
    }

    /**
     * Deposit amount into wallet. Updates valide_balance and creates a transaction record.
     */
    public function deposit(int $walletId, float $amount, ?string $notes = null): Transaction
    {
        $wallet = Wallet::lockForUpdate()->findOrFail($walletId);

        DB::beginTransaction();
        try {
            $before = (float) $wallet->valide_balance;
            $after = $before + $amount;

            $wallet->valide_balance = $after;
            $wallet->save();

            $transaction = $wallet->transactions()->create([
                'amount'         => $amount,
                'type'           => 'deposit',
                'created_by'     => auth()->id(),
                'notes'          => $notes,
                'before_balance' => $before,
                'after_balance'  => $after,
            ]);

            DB::commit();
            return $transaction;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
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
}
