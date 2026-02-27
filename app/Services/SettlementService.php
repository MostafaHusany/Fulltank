<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;

use App\Models\Wallet;
use App\Models\Station;
use App\Models\Settlement;
use App\Models\Transaction;

class SettlementService
{
    public function generateReferenceNo(): string
    {
        $prefix = 'SET';
        $date = now()->format('Ymd');
        $random = strtoupper(Str::random(6));
        return "{$prefix}{$date}{$random}";
    }

    public function getStationWallet(int $stationId): ?Wallet
    {
        return Wallet::whereHas('station', function ($q) use ($stationId) {
            $q->where('id', $stationId);
        })->first();
    }

    public function createSettlement(array $data, ?UploadedFile $receiptImage, int $adminId): Settlement
    {
        $stationId = $data['station_id'];
        $amount = (float) $data['amount'];

        $stationWallet = $this->getStationWallet($stationId);

        if (!$stationWallet) {
            throw new Exception(__('settlements.station_wallet_not_found'));
        }

        if (!$stationWallet->is_active) {
            throw new Exception(__('settlements.station_wallet_inactive'));
        }

        $currentBalance = (float) $stationWallet->valide_balance;

        if ($amount > $currentBalance) {
            throw new Exception(__('settlements.amount_exceeds_balance'));
        }

        if ($amount <= 0) {
            throw new Exception(__('settlements.invalid_amount'));
        }

        return DB::transaction(function () use ($data, $receiptImage, $adminId, $stationWallet, $amount) {
            $imagePath = null;
            if ($receiptImage) {
                $imagePath = $this->storeReceiptImage($receiptImage);
            }

            $beforeBalance = (float) $stationWallet->valide_balance;
            $stationWallet->valide_balance -= $amount;
            $stationWallet->save();

            Transaction::create([
                'wallet_id'      => $stationWallet->id,
                'amount'         => -$amount,
                'type'           => 'withdrawal',
                'created_by'     => $adminId,
                'notes'          => 'Settlement: ' . $this->generateReferenceNo(),
                'before_balance' => $beforeBalance,
                'after_balance'  => $stationWallet->valide_balance,
            ]);

            $settlement = Settlement::create([
                'reference_no'        => $this->generateReferenceNo(),
                'station_id'          => $data['station_id'],
                'amount'              => $amount,
                'payment_method'      => $data['payment_method'],
                'transaction_details' => $data['transaction_details'] ?? null,
                'receipt_image'       => $imagePath,
                'admin_id'            => $adminId,
            ]);

            $settlement->load(['station', 'admin']);

            return $settlement;
        });
    }

    public function getStationsWithBalances()
    {
        return Station::query()
            ->with(['governorate:id,name', 'district:id,name'])
            ->whereHas('wallet')
            ->withSum('wallet as unsettled_balance', 'valide_balance')
            ->get()
            ->map(function ($station) {
                $lastSettlement = Settlement::where('station_id', $station->id)
                    ->orderBy('created_at', 'desc')
                    ->first();

                $station->last_settlement_date = $lastSettlement ? $lastSettlement->created_at : null;
                $station->last_settlement_amount = $lastSettlement ? $lastSettlement->amount : null;
                $station->wallet_is_active = $station->wallet ? $station->wallet->is_active : false;

                return $station;
            });
    }

    private function storeReceiptImage(UploadedFile $file): string
    {
        $year = now()->format('Y');
        $directory = "settlements/{$year}";

        $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs($directory, $filename, 'public');

        return $path;
    }

    public function getReceiptImageUrl(?string $path): ?string
    {
        if (!$path) {
            return null;
        }

        return Storage::disk('public')->url($path);
    }
}
