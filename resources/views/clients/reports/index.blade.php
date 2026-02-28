@extends('layouts.clients.app')

@push('title')
    <h1 class="h2">@lang('client.reports.title')</h1>
@endpush

@section('content')
<div class="container-fluid px-0">
    
    {{-- Report Cards --}}
    <div class="row g-4">
        {{-- Vehicle Consumption Report --}}
        <div class="col-md-6 col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center py-4">
                    <div class="bg-primary bg-opacity-10 rounded-circle d-inline-flex p-4 mb-3">
                        <i class="fas fa-car fa-2x text-primary"></i>
                    </div>
                    <h5 class="card-title">@lang('client.reports.vehicle_consumption')</h5>
                    <p class="text-muted small">@lang('client.reports.vehicle_consumption_desc')</p>
                    <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#vehicleReportModal">
                        <i class="fas fa-chart-bar me-1"></i>@lang('client.reports.generate')
                    </button>
                </div>
            </div>
        </div>

        {{-- Driver Activity Report --}}
        <div class="col-md-6 col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center py-4">
                    <div class="bg-info bg-opacity-10 rounded-circle d-inline-flex p-4 mb-3">
                        <i class="fas fa-id-card fa-2x text-info"></i>
                    </div>
                    <h5 class="card-title">@lang('client.reports.driver_activity')</h5>
                    <p class="text-muted small">@lang('client.reports.driver_activity_desc')</p>
                    <button type="button" class="btn btn-outline-info" data-bs-toggle="modal" data-bs-target="#driverReportModal">
                        <i class="fas fa-chart-line me-1"></i>@lang('client.reports.generate')
                    </button>
                </div>
            </div>
        </div>

        {{-- Account Statement --}}
        <div class="col-md-6 col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center py-4">
                    <div class="bg-success bg-opacity-10 rounded-circle d-inline-flex p-4 mb-3">
                        <i class="fas fa-file-invoice-dollar fa-2x text-success"></i>
                    </div>
                    <h5 class="card-title">@lang('client.reports.statement')</h5>
                    <p class="text-muted small">@lang('client.reports.statement_desc')</p>
                    <button type="button" class="btn btn-outline-success" data-bs-toggle="modal" data-bs-target="#statementModal">
                        <i class="fas fa-receipt me-1"></i>@lang('client.reports.generate')
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Results Container --}}
    <div class="card border-0 shadow-sm mt-4" id="report-results" style="display: none;">
        <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
            <h5 class="mb-0" id="report-title"></h5>
            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="$('#report-results').hide()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="card-body" id="report-content">
        </div>
    </div>

</div>

{{-- Vehicle Report Modal --}}
<div class="modal fade" id="vehicleReportModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">@lang('client.reports.vehicle_consumption')</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="vehicle-report-form">
                    <div class="mb-3">
                        <label class="form-label">@lang('client.reports.start_date')</label>
                        <input type="date" class="form-control" name="start_date" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">@lang('client.reports.end_date')</label>
                        <input type="date" class="form-control" name="end_date" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">@lang('client.reports.cancel')</button>
                <button type="button" class="btn btn-primary" id="generate-vehicle-report">@lang('client.reports.generate')</button>
            </div>
        </div>
    </div>
</div>

{{-- Driver Report Modal --}}
<div class="modal fade" id="driverReportModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">@lang('client.reports.driver_activity')</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="driver-report-form">
                    <div class="mb-3">
                        <label class="form-label">@lang('client.reports.start_date')</label>
                        <input type="date" class="form-control" name="start_date" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">@lang('client.reports.end_date')</label>
                        <input type="date" class="form-control" name="end_date" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">@lang('client.reports.cancel')</button>
                <button type="button" class="btn btn-info" id="generate-driver-report">@lang('client.reports.generate')</button>
            </div>
        </div>
    </div>
</div>

{{-- Statement Modal --}}
<div class="modal fade" id="statementModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">@lang('client.reports.statement')</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="statement-form">
                    <div class="mb-3">
                        <label class="form-label">@lang('client.reports.start_date')</label>
                        <input type="date" class="form-control" name="start_date" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">@lang('client.reports.end_date')</label>
                        <input type="date" class="form-control" name="end_date" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">@lang('client.reports.cancel')</button>
                <button type="button" class="btn btn-success" id="generate-statement">@lang('client.reports.generate')</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('custome-js')
<script>
(function() {
    $('#generate-vehicle-report').on('click', function() {
        var formData = $('#vehicle-report-form').serialize();
        
        $.ajax({
            url: '{{ route("client.reports.vehicleConsumption") }}',
            method: 'GET',
            data: formData,
            success: function(response) {
                if (response.success) {
                    displayVehicleReport(response.data);
                    $('#vehicleReportModal').modal('hide');
                }
            },
            error: function() {
                failerToast('@lang("client.reports.error")');
            }
        });
    });

    $('#generate-driver-report').on('click', function() {
        var formData = $('#driver-report-form').serialize();
        
        $.ajax({
            url: '{{ route("client.reports.driverActivity") }}',
            method: 'GET',
            data: formData,
            success: function(response) {
                if (response.success) {
                    displayDriverReport(response.data);
                    $('#driverReportModal').modal('hide');
                }
            },
            error: function() {
                failerToast('@lang("client.reports.error")');
            }
        });
    });

    $('#generate-statement').on('click', function() {
        var formData = $('#statement-form').serialize();
        
        $.ajax({
            url: '{{ route("client.reports.statement") }}',
            method: 'GET',
            data: formData,
            success: function(response) {
                if (response.success) {
                    displayStatement(response.data);
                    $('#statementModal').modal('hide');
                }
            },
            error: function() {
                failerToast('@lang("client.reports.error")');
            }
        });
    });

    function displayVehicleReport(data) {
        var html = '<div class="table-responsive"><table class="table table-striped">';
        html += '<thead><tr><th>@lang("client.reports.vehicle")</th><th>@lang("client.reports.transactions")</th><th>@lang("client.reports.liters")</th><th>@lang("client.reports.amount")</th></tr></thead><tbody>';
        
        data.vehicles.forEach(function(v) {
            html += '<tr>';
            html += '<td>' + v.plate_number + '</td>';
            html += '<td>' + v.transaction_count + '</td>';
            html += '<td>' + parseFloat(v.total_liters || 0).toFixed(2) + '</td>';
            html += '<td>' + parseFloat(v.total_amount || 0).toFixed(2) + '</td>';
            html += '</tr>';
        });
        
        html += '</tbody><tfoot class="table-light"><tr>';
        html += '<th>@lang("client.reports.total")</th>';
        html += '<th>' + data.totals.transaction_count + '</th>';
        html += '<th>' + parseFloat(data.totals.total_liters).toFixed(2) + '</th>';
        html += '<th>' + parseFloat(data.totals.total_amount).toFixed(2) + '</th>';
        html += '</tr></tfoot></table></div>';
        
        $('#report-title').text('@lang("client.reports.vehicle_consumption") (' + data.period.start + ' - ' + data.period.end + ')');
        $('#report-content').html(html);
        $('#report-results').show();
    }

    function displayDriverReport(data) {
        var html = '<div class="table-responsive"><table class="table table-striped">';
        html += '<thead><tr><th>@lang("client.reports.driver")</th><th>@lang("client.reports.transactions")</th><th>@lang("client.reports.liters")</th><th>@lang("client.reports.amount")</th></tr></thead><tbody>';
        
        data.drivers.forEach(function(d) {
            html += '<tr>';
            html += '<td>' + (d.driver ? d.driver.name : '-') + '</td>';
            html += '<td>' + d.transaction_count + '</td>';
            html += '<td>' + parseFloat(d.total_liters || 0).toFixed(2) + '</td>';
            html += '<td>' + parseFloat(d.total_amount || 0).toFixed(2) + '</td>';
            html += '</tr>';
        });
        
        html += '</tbody></table></div>';
        
        $('#report-title').text('@lang("client.reports.driver_activity") (' + data.period.start + ' - ' + data.period.end + ')');
        $('#report-content').html(html);
        $('#report-results').show();
    }

    function displayStatement(data) {
        var html = '<div class="row mb-3">';
        html += '<div class="col-md-4"><div class="card bg-success text-white"><div class="card-body text-center"><small>@lang("client.reports.deposits")</small><h4>' + parseFloat(data.summary.deposits).toFixed(2) + '</h4></div></div></div>';
        html += '<div class="col-md-4"><div class="card bg-danger text-white"><div class="card-body text-center"><small>@lang("client.reports.withdrawals")</small><h4>' + parseFloat(data.summary.withdrawals).toFixed(2) + '</h4></div></div></div>';
        html += '<div class="col-md-4"><div class="card bg-primary text-white"><div class="card-body text-center"><small>@lang("client.reports.current_balance")</small><h4>' + parseFloat(data.summary.current_balance).toFixed(2) + '</h4></div></div></div>';
        html += '</div>';
        
        html += '<div class="table-responsive"><table class="table table-striped">';
        html += '<thead><tr><th>@lang("client.reports.date")</th><th>@lang("client.reports.type")</th><th>@lang("client.reports.amount")</th><th>@lang("client.reports.balance")</th></tr></thead><tbody>';
        
        data.transactions.forEach(function(t) {
            var color = parseFloat(t.amount) >= 0 ? 'text-success' : 'text-danger';
            html += '<tr>';
            html += '<td>' + t.created_at.substring(0, 10) + '</td>';
            html += '<td>' + t.type + '</td>';
            html += '<td class="' + color + '">' + parseFloat(t.amount).toFixed(2) + '</td>';
            html += '<td>' + parseFloat(t.after_balance).toFixed(2) + '</td>';
            html += '</tr>';
        });
        
        html += '</tbody></table></div>';
        
        $('#report-title').text('@lang("client.reports.statement") (' + data.period.start + ' - ' + data.period.end + ')');
        $('#report-content').html(html);
        $('#report-results').show();
    }
})();
</script>
@endpush
