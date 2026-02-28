@extends('layouts.clients.app')

@push('title')
    <h1 class="h2">@lang('client.transactions.title')</h1>
@endpush

@push('custome-plugin')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
@endpush

@section('content')
<div class="container-fluid px-0">

    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">@lang('client.transactions.start_date')</label>
                    <input type="date" class="form-control" id="start_date">
                </div>
                <div class="col-md-3">
                    <label class="form-label">@lang('client.transactions.end_date')</label>
                    <input type="date" class="form-control" id="end_date">
                </div>
                <div class="col-md-4">
                    <label class="form-label">&nbsp;</label>
                    <div>
                        <button type="button" class="btn btn-success" id="filter-btn">
                            <i class="fas fa-filter me-1"></i>@lang('client.transactions.filter')
                        </button>
                        <button type="button" class="btn btn-outline-secondary" id="clear-btn">
                            <i class="fas fa-times me-1"></i>@lang('client.transactions.clear')
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table id="transactions-table" class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>@lang('client.transactions.ref')</th>
                            <th>@lang('client.transactions.vehicle')</th>
                            <th>@lang('client.transactions.driver')</th>
                            <th>@lang('client.transactions.station')</th>
                            <th>@lang('client.transactions.fuel_type')</th>
                            <th>@lang('client.transactions.liters')</th>
                            <th>@lang('client.transactions.amount')</th>
                            <th>@lang('client.transactions.status')</th>
                            <th>@lang('client.transactions.date')</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>

</div>
@endsection

@push('custome-js')
<script>
(function() {
    var table = $('#transactions-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route("client.transactions.index") }}',
            data: function(d) {
                d.start_date = $('#start_date').val();
                d.end_date   = $('#end_date').val();
            }
        },
        columns: [
            { data: 'reference_no', name: 'reference_no' },
            { data: 'vehicle_plate', name: 'vehicle_plate', orderable: false },
            { data: 'driver_name', name: 'driver_name', orderable: false },
            { data: 'station_name', name: 'station_name', orderable: false },
            { data: 'fuel_type_name', name: 'fuel_type_name', orderable: false },
            { 
                data: 'actual_liters', 
                name: 'actual_liters',
                render: function(data) { return parseFloat(data).toFixed(2); }
            },
            { 
                data: 'total_amount', 
                name: 'total_amount',
                render: function(data) { return parseFloat(data).toFixed(2); }
            },
            { data: 'status_badge', name: 'status_badge', orderable: false },
            { data: 'formatted_date', name: 'created_at' }
        ],
        order: [[8, 'desc']],
        language: {
            url: window.is_ar ? '//cdn.datatables.net/plug-ins/1.13.4/i18n/ar.json' : ''
        }
    });

    $('#filter-btn').on('click', function() {
        table.ajax.reload();
    });

    $('#clear-btn').on('click', function() {
        $('#start_date').val('');
        $('#end_date').val('');
        table.ajax.reload();
    });
})();
</script>
@endpush
