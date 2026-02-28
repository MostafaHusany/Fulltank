<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class StationTransactionsExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected Collection $transactions;

    public function __construct(Collection $transactions)
    {
        $this->transactions = $transactions;
    }

    public function collection(): Collection
    {
        return $this->transactions;
    }

    public function headings(): array
    {
        return [
            __('station.transactions.export_headers.reference'),
            __('station.transactions.export_headers.date'),
            __('station.transactions.export_headers.time'),
            __('station.transactions.export_headers.vehicle'),
            __('station.transactions.export_headers.client'),
            __('station.transactions.export_headers.worker'),
            __('station.transactions.export_headers.fuel_type'),
            __('station.transactions.export_headers.price_per_liter'),
            __('station.transactions.export_headers.liters'),
            __('station.transactions.export_headers.amount'),
            __('station.transactions.export_headers.status'),
        ];
    }

    public function map($transaction): array
    {
        return [
            $transaction->reference_no,
            $transaction->created_at->format('Y-m-d'),
            $transaction->created_at->format('H:i:s'),
            $transaction->vehicle ? $transaction->vehicle->plate_number : '-',
            $transaction->client ? ($transaction->client->company_name ?: $transaction->client->name) : '-',
            $transaction->worker ? $transaction->worker->full_name : '-',
            $transaction->fuelType ? $transaction->fuelType->name : '-',
            number_format((float) $transaction->price_per_liter, 2),
            number_format((float) $transaction->actual_liters, 3),
            number_format((float) $transaction->total_amount, 2),
            __('station.transactions.status_' . $transaction->status),
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
