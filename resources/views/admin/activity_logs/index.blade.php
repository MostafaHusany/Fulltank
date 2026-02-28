@extends('layouts.admin.app')

@push('title')
    <h1 class="h2">@lang('activity_logs.Title')</h1>
@endpush

@push('custome-css')
<style>
    .stat-card { border-radius: 10px; transition: transform 0.2s; }
    .stat-card:hover { transform: translateY(-3px); }
    .stat-value { font-size: 1.5rem; font-weight: 700; }
    .changes-table th { width: 30%; background-color: #f8f9fa; }
    .changes-table td { word-break: break-word; }
    .old-value { background-color: #ffebee; }
    .new-value { background-color: #e8f5e9; }
    .json-view { background-color: #f5f5f5; padding: 10px; border-radius: 5px; font-family: monospace; font-size: 12px; max-height: 300px; overflow-y: auto; }
</style>
@endpush

@section('content')
    <!-- Stats Cards -->
    <div class="row mb-4" id="stats-container">
        <div class="col-md-3">
            <div class="card stat-card border-primary">
                <div class="card-body text-center">
                    <i class="fas fa-calendar-day fa-2x text-primary mb-2"></i>
                    <p class="mb-0 text-muted">@lang('activity_logs.Today')</p>
                    <p class="stat-value text-primary" id="stat-today">---</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card border-success">
                <div class="card-body text-center">
                    <i class="fas fa-calendar-week fa-2x text-success mb-2"></i>
                    <p class="mb-0 text-muted">@lang('activity_logs.This Week')</p>
                    <p class="stat-value text-success" id="stat-week">---</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card border-info">
                <div class="card-body text-center">
                    <i class="fas fa-calendar-alt fa-2x text-info mb-2"></i>
                    <p class="mb-0 text-muted">@lang('activity_logs.This Month')</p>
                    <p class="stat-value text-info" id="stat-month">---</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card border-secondary">
                <div class="card-body text-center">
                    <i class="fas fa-database fa-2x text-secondary mb-2"></i>
                    <p class="mb-0 text-muted">@lang('activity_logs.Total Logs')</p>
                    <p class="stat-value text-secondary" id="stat-total">---</p>
                </div>
            </div>
        </div>
    </div>

    <div id="objectsCard" class="card">
        <div class="card-header">
            <div class="row">
                <div class="col-6 pt-1">
                    @lang('activity_logs.Title Administration')
                </div>
                <div class="col-6 text-end">
                    <button class="relode-btn btn btn-sm btn-outline-dark">
                        <i class="relode-btn-icon fas fa-sync-alt"></i>
                        <span class="relode-btn-loader spinner-grow spinner-grow-sm" style="display: none;" role="status" aria-hidden="true"></span>
                    </button>

                    <button class="btn btn-sm btn-outline-dark toggle-search">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </div>
        </div>

        <div class="card-body custome-table">
            @include('admin.activity_logs.incs._search')

            <div style="overflow-x: scroll">
                <table id="dataTable" class="table text-center">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>@lang('activity_logs.User')</th>
                            <th>@lang('activity_logs.Action')</th>
                            <th>@lang('activity_logs.Subject')</th>
                            <th>@lang('activity_logs.IP Address')</th>
                            <th>@lang('activity_logs.Date')</th>
                            <th>@lang('layouts.Actions')</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>

@include('admin.activity_logs.incs._details_modal')

@endSection

@push('custome-js')
<script>
$(document).ready(function () {
    window.is_ar = '{{ $is_ar }}';

    const ROUTES = {
        index : "{{ route('admin.activityLogs.index') }}",
        show  : "{{ route('admin.activityLogs.index') }}",
        stats : "{{ route('admin.activityLogs.stats') }}"
    };

    const objects_dynamic_table = new DynamicTable(
        {
            index_route   : ROUTES.index,
            store_route   : ROUTES.index,
            show_route    : ROUTES.index,
            update_route  : ROUTES.index,
            destroy_route : ROUTES.index
        },
        '#dataTable',
        {
            success_el : '#successAlert',
            danger_el  : '#dangerAlert',
            warning_el : '#warningAlert'
        },
        {
            table_id        : '#dataTable',
            toggle_btn      : '.toggle-btn',
            create_obj_btn  : '.create-object',
            update_obj_btn  : '.update-object',
            fields_list     : [],
            imgs_fields     : []
        },
        [
            { data: 'id',              name: 'id' },
            { data: 'causer_name',     name: 'causer_name' },
            { data: 'event_badge',     name: 'event_badge' },
            { data: 'subject_label',   name: 'subject_label' },
            { data: 'ip_address',      name: 'ip_address' },
            { data: 'formatted_date',  name: 'formatted_date' },
            { data: 'actions',         name: 'actions' },
        ],
        function (d) {
            if ($('#s-causer_id').length)
                d.causer_id = $('#s-causer_id').val();

            if ($('#s-subject_type').length)
                d.subject_type = $('#s-subject_type').val();

            if ($('#s-event').length)
                d.event = $('#s-event').val();

            if ($('#s-date_from').length)
                d.date_from = $('#s-date_from').val();

            if ($('#s-date_to').length)
                d.date_to = $('#s-date_to').val();
        }
    );

    $('#s-causer_id').select2({
        allowClear: true,
        width: '100%',
        placeholder: '@lang("activity_logs.Select User")'
    });

    loadStats();

    function loadStats() {
        axios.get(ROUTES.stats)
            .then(function(response) {
                if (response.data.success) {
                    let data = response.data.data;
                    $('#stat-today').text(data.today);
                    $('#stat-week').text(data.this_week);
                    $('#stat-month').text(data.this_month);
                    $('#stat-total').text(data.total);
                }
            });
    }

    $(document).on('click', '.view-details', function () {
        let logId = $(this).data('id');

        $('#detailsModalLabel').text('@lang("activity_logs.Log Details") #' + logId);
        $('#details-loading').show();
        $('#details-content').hide();
        $('#detailsModal').modal('show');

        axios.get(ROUTES.show + '/' + logId)
            .then(function(response) {
                $('#details-loading').hide();

                if (!response.data.success) {
                    window.failerToast(response.data.msg);
                    return;
                }

                let data = response.data.data;
                renderDetails(data);
                $('#details-content').show();
            })
            .catch(function(error) {
                $('#details-loading').hide();
                window.failerToast('@lang("activity_logs.Error loading details")');
            });
    });

    function renderDetails(data) {
        let html = '';

        html += '<div class="row mb-3">';
        html += '<div class="col-md-6">';
        html += '<table class="table table-sm table-bordered">';
        html += '<tr><th>@lang("activity_logs.Event")</th><td>' + getEventBadge(data.event) + '</td></tr>';
        html += '<tr><th>@lang("activity_logs.Subject")</th><td>' + data.subject + '</td></tr>';
        html += '<tr><th>@lang("activity_logs.User")</th><td>' + (data.causer ? data.causer.name : 'System') + '</td></tr>';
        html += '<tr><th>@lang("activity_logs.Date")</th><td>' + data.created_at + '</td></tr>';
        html += '</table>';
        html += '</div>';
        html += '<div class="col-md-6">';
        html += '<table class="table table-sm table-bordered">';
        html += '<tr><th>@lang("activity_logs.IP Address")</th><td>' + (data.ip_address || '---') + '</td></tr>';
        html += '<tr><th>@lang("activity_logs.User Agent")</th><td style="word-break:break-all;font-size:11px;">' + (data.user_agent || '---') + '</td></tr>';
        html += '</table>';
        html += '</div>';
        html += '</div>';

        if (data.event === 'updated' && Object.keys(data.changes).length > 0) {
            html += '<h6 class="mt-3"><i class="fas fa-exchange-alt me-2"></i>@lang("activity_logs.Changes")</h6>';
            html += '<table class="table table-bordered table-sm changes-table">';
            html += '<thead><tr><th>@lang("activity_logs.Field")</th><th class="old-value">@lang("activity_logs.Old Value")</th><th class="new-value">@lang("activity_logs.New Value")</th></tr></thead>';
            html += '<tbody>';

            for (let field in data.changes) {
                let change = data.changes[field];
                html += '<tr>';
                html += '<td><strong>' + field + '</strong></td>';
                html += '<td class="old-value">' + formatValue(change.old) + '</td>';
                html += '<td class="new-value">' + formatValue(change.new) + '</td>';
                html += '</tr>';
            }

            html += '</tbody></table>';
        } else if (data.event === 'created' && Object.keys(data.new).length > 0) {
            html += '<h6 class="mt-3"><i class="fas fa-plus me-2"></i>@lang("activity_logs.Created With")</h6>';
            html += '<div class="json-view"><pre>' + JSON.stringify(data.new, null, 2) + '</pre></div>';
        } else if (data.event === 'deleted' && Object.keys(data.old).length > 0) {
            html += '<h6 class="mt-3"><i class="fas fa-trash me-2"></i>@lang("activity_logs.Deleted Data")</h6>';
            html += '<div class="json-view"><pre>' + JSON.stringify(data.old, null, 2) + '</pre></div>';
        }

        $('#details-body').html(html);
    }

    function getEventBadge(event) {
        let badges = {
            'created': '<span class="badge bg-success"><i class="fas fa-plus me-1"></i>Created</span>',
            'updated': '<span class="badge bg-warning text-dark"><i class="fas fa-edit me-1"></i>Updated</span>',
            'deleted': '<span class="badge bg-danger"><i class="fas fa-trash me-1"></i>Deleted</span>'
        };
        return badges[event] || '<span class="badge bg-secondary">' + event + '</span>';
    }

    function formatValue(val) {
        if (val === null || val === undefined) return '<em class="text-muted">null</em>';
        if (typeof val === 'object') return '<code>' + JSON.stringify(val) + '</code>';
        if (typeof val === 'boolean') return val ? '<span class="badge bg-success">true</span>' : '<span class="badge bg-danger">false</span>';
        return escapeHtml(String(val));
    }

    function escapeHtml(str) {
        let div = document.createElement('div');
        div.appendChild(document.createTextNode(str));
        return div.innerHTML;
    }
});
</script>
@endpush
