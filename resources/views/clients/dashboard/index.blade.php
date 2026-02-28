@extends('layouts.clients.app')

@push('title')
    <h1 class="h2">@lang('client.dashboard.title')</h1>
@endpush

@section('content')
<div class="container-fluid px-0">

    {{-- Statistics Cards Row --}}
    <div class="row g-3 mb-4">
        {{-- Wallet Balance Card --}}
        <div class="col-md-6 col-lg-3">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted mb-1">@lang('client.dashboard.wallet_balance')</p>
                            <h3 class="fw-bold text-success mb-0" id="stat-wallet-balance">{{ $stats['wallet_balance'] }} @lang('client.currency')</h3>
                        </div>
                        <div class="bg-success bg-opacity-10 p-3 rounded">
                            <i class="fas fa-wallet fa-2x text-success"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Vehicles Card --}}
        <div class="col-md-6 col-lg-3">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted mb-1">@lang('client.dashboard.vehicles')</p>
                            <h3 class="fw-bold text-primary mb-0" id="stat-vehicle-count">{{ $stats['vehicle_count'] }}</h3>
                            <small class="text-muted"><span id="stat-active-vehicles">{{ $stats['active_vehicles'] }}</span> @lang('client.dashboard.active')</small>
                        </div>
                        <div class="bg-primary bg-opacity-10 p-3 rounded">
                            <i class="fas fa-car fa-2x text-primary"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Drivers Card --}}
        <div class="col-md-6 col-lg-3">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted mb-1">@lang('client.dashboard.drivers')</p>
                            <h3 class="fw-bold text-info mb-0" id="stat-driver-count">{{ $stats['driver_count'] }}</h3>
                            <small class="text-muted"><span id="stat-active-drivers">{{ $stats['active_drivers'] }}</span> @lang('client.dashboard.active')</small>
                        </div>
                        <div class="bg-info bg-opacity-10 p-3 rounded">
                            <i class="fas fa-id-card fa-2x text-info"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Today's Transactions Card --}}
        <div class="col-md-6 col-lg-3">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted mb-1">@lang('client.dashboard.today_transactions')</p>
                            <h3 class="fw-bold text-warning mb-0" id="stat-today-transactions">{{ $stats['today_transactions'] }}</h3>
                            <small class="text-muted"><span id="stat-today-liters">{{ $stats['today_liters'] }}</span> @lang('client.dashboard.liters')</small>
                        </div>
                        <div class="bg-warning bg-opacity-10 p-3 rounded">
                            <i class="fas fa-gas-pump fa-2x text-warning"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Monthly Summary Row --}}
    <div class="row g-3 mb-4">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0">
                    <h5 class="mb-0"><i class="fas fa-calendar-alt me-2"></i>@lang('client.dashboard.monthly_summary')</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-6 text-center border-end">
                            <h4 class="fw-bold text-primary mb-1" id="stat-monthly-liters">{{ $stats['monthly_liters'] }}</h4>
                            <small class="text-muted">@lang('client.dashboard.liters')</small>
                        </div>
                        <div class="col-6 text-center">
                            <h4 class="fw-bold text-success mb-1" id="stat-monthly-amount">{{ $stats['monthly_amount'] }} @lang('client.currency')</h4>
                            <small class="text-muted">@lang('client.dashboard.total_spent')</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0">
                    <h5 class="mb-0"><i class="fas fa-clock me-2"></i>@lang('client.dashboard.today_summary')</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-6 text-center border-end">
                            <h4 class="fw-bold text-primary mb-1" id="stat-today-liters-2">{{ $stats['today_liters'] }}</h4>
                            <small class="text-muted">@lang('client.dashboard.liters')</small>
                        </div>
                        <div class="col-6 text-center">
                            <h4 class="fw-bold text-success mb-1" id="stat-today-amount">{{ $stats['today_amount'] }} @lang('client.currency')</h4>
                            <small class="text-muted">@lang('client.dashboard.total_spent')</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Recent Transactions Table --}}
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-history me-2"></i>@lang('client.dashboard.recent_transactions')</h5>
                    <a href="{{ route('client.transactions.index') }}" class="btn btn-sm btn-outline-primary">
                        @lang('client.dashboard.view_all')
                    </a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>@lang('client.dashboard.table.vehicle')</th>
                                    <th>@lang('client.dashboard.table.station')</th>
                                    <th>@lang('client.dashboard.table.fuel_type')</th>
                                    <th>@lang('client.dashboard.table.liters')</th>
                                    <th>@lang('client.dashboard.table.amount')</th>
                                    <th>@lang('client.dashboard.table.date')</th>
                                </tr>
                            </thead>
                            <tbody id="recent-transactions-body">
                                @forelse($stats['recent_transactions'] as $transaction)
                                <tr>
                                    <td>{{ $transaction->vehicle->plate_number ?? '-' }}</td>
                                    <td>{{ $transaction->station->name ?? '-' }}</td>
                                    <td>{{ $transaction->fuelType->name ?? '-' }}</td>
                                    <td>{{ number_format($transaction->actual_liters, 2) }}</td>
                                    <td>{{ number_format($transaction->total_amount, 2) }} @lang('client.currency')</td>
                                    <td>{{ $transaction->created_at->format('Y-m-d H:i') }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">
                                        @lang('client.dashboard.no_transactions')
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection

@push('custome-js')
<script>
(function() {
    function refreshStats() {
        $.ajax({
            url: '{{ route("client.dashboard.stats") }}',
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    const data = response.data;
                    
                    $('#stat-wallet-balance').text(data.wallet_balance + ' @lang("client.currency")');
                    $('#stat-vehicle-count').text(data.vehicle_count);
                    $('#stat-active-vehicles').text(data.active_vehicles);
                    $('#stat-driver-count').text(data.driver_count);
                    $('#stat-active-drivers').text(data.active_drivers);
                    $('#stat-today-transactions').text(data.today_transactions);
                    $('#stat-today-liters').text(data.today_liters);
                    $('#stat-today-liters-2').text(data.today_liters);
                    $('#stat-today-amount').text(data.today_amount + ' @lang("client.currency")');
                    $('#stat-monthly-liters').text(data.monthly_liters);
                    $('#stat-monthly-amount').text(data.monthly_amount + ' @lang("client.currency")');
                    
                    $('#wallet-balance-display').text(data.wallet_balance + ' @lang("client.currency")');
                }
            }
        });
    }

    setInterval(refreshStats, 60000);
})();
</script>
@endpush
