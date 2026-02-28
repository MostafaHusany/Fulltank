@extends('layouts.station.app')

@push('title')
    <h4 class="h4">@lang('station.workers.title')</h4>
@endpush

@push('custome-plugin')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endpush

@push('custome-css')
<style>
    .worker-stats {
        font-size: 0.85rem;
    }
    .worker-stats .badge {
        font-weight: 500;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    {{-- Action Bar --}}
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <div class="input-group">
                        <span class="input-group-text bg-white"><i class="fas fa-search"></i></span>
                        <input type="text" class="form-control" id="searchInput" placeholder="@lang('station.workers.search_placeholder')">
                    </div>
                </div>
                <div class="col-md-6 text-md-end mt-3 mt-md-0">
                    <button type="button" class="btn btn-station" data-bs-toggle="modal" data-bs-target="#addWorkerModal">
                        <i class="fas fa-plus me-1"></i>@lang('station.workers.add_worker')
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Workers Table --}}
    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover" id="workersTable">
                    <thead class="table-light">
                        <tr>
                            <th>@lang('station.workers.name')</th>
                            <th>@lang('station.workers.username')</th>
                            <th>@lang('station.workers.phone')</th>
                            <th>@lang('station.workers.today_transactions')</th>
                            <th>@lang('station.workers.status')</th>
                            <th class="text-center">@lang('station.workers.actions')</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- Add Worker Modal --}}
@include('station.workers.incs._create')

{{-- Edit Worker Modal --}}
@include('station.workers.incs._edit')

{{-- Quick View Modal --}}
@include('station.workers.incs._quick_view')
@endsection

@push('custome-js')
<script>
$(document).ready(function() {
    var searchTimer;
    var workersTable = $('#workersTable').DataTable({
        processing: true,
        serverSide: false,
        ajax: {
            url: '{{ route("station.workers.index") }}',
            dataSrc: 'data'
        },
        columns: [
            { 
                data: 'name',
                render: function(data, type, row) {
                    return '<div class="fw-semibold">' + data + '</div>';
                }
            },
            { 
                data: 'username',
                render: function(data) {
                    return data ? '<code>' + data + '</code>' : '-';
                }
            },
            { 
                data: 'phone',
                render: function(data) {
                    return data || '-';
                }
            },
            { 
                data: 'today_transactions',
                render: function(data, type, row) {
                    var liters = parseFloat(row.today_liters || 0).toFixed(1);
                    return '<span class="badge bg-info">' + data + '</span> <small class="text-muted">(' + liters + ' L)</small>';
                }
            },
            { 
                data: 'is_active',
                render: function(data, type, row) {
                    if (data) {
                        return '<span class="badge bg-success">@lang("station.workers.active")</span>';
                    }
                    return '<span class="badge bg-danger">@lang("station.workers.inactive")</span>';
                }
            },
            {
                data: 'id',
                className: 'text-center',
                orderable: false,
                render: function(data, type, row) {
                    return `
                        <div class="btn-group btn-group-sm">
                            <button type="button" class="btn btn-outline-info btn-quick-view" data-id="${data}" title="@lang('station.workers.quick_view')">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button type="button" class="btn btn-outline-primary btn-edit" data-id="${data}" data-name="${row.name}" data-phone="${row.phone || ''}" data-username="${row.username || ''}" title="@lang('station.workers.edit')">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button type="button" class="btn btn-outline-${row.is_active ? 'warning' : 'success'} btn-toggle" data-id="${data}" data-active="${row.is_active ? 1 : 0}" title="${row.is_active ? '@lang("station.workers.deactivate")' : '@lang("station.workers.activate")'}">
                                <i class="fas fa-${row.is_active ? 'ban' : 'check'}"></i>
                            </button>
                            <button type="button" class="btn btn-outline-danger btn-delete" data-id="${data}" title="@lang('station.workers.delete')">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    `;
                }
            }
        ],
        order: [[0, 'asc']],
        language: {
            url: window.is_ar ? '//cdn.datatables.net/plug-ins/1.13.4/i18n/ar.json' : ''
        }
    });

    $('#searchInput').on('keyup', function() {
        clearTimeout(searchTimer);
        var search = $(this).val();
        searchTimer = setTimeout(function() {
            workersTable.ajax.url('{{ route("station.workers.index") }}?search=' + encodeURIComponent(search)).load();
        }, 400);
    });

    $('#addWorkerForm').on('submit', function(e) {
        e.preventDefault();
        var form = $(this);
        var btn = form.find('button[type="submit"]');
        
        btn.prop('disabled', true);
        
        $.ajax({
            url: '{{ route("station.workers.store") }}',
            method: 'POST',
            data: form.serialize(),
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            success: function(res) {
                if (res.success) {
                    $('#addWorkerModal').modal('hide');
                    form[0].reset();
                    workersTable.ajax.reload();
                    Swal.fire({
                        icon: 'success',
                        title: '@lang("layouts.success")',
                        text: res.message,
                        timer: 2000,
                        showConfirmButton: false
                    });
                }
            },
            error: function(xhr) {
                var errors = xhr.responseJSON?.errors;
                if (errors) {
                    var errorHtml = Object.values(errors).flat().join('<br>');
                    Swal.fire({
                        icon: 'error',
                        title: '@lang("layouts.error")',
                        html: errorHtml
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: '@lang("layouts.error")',
                        text: xhr.responseJSON?.message || '@lang("station.workers.create_error")'
                    });
                }
            },
            complete: function() {
                btn.prop('disabled', false);
            }
        });
    });

    $(document).on('click', '.btn-edit', function() {
        var id = $(this).data('id');
        var name = $(this).data('name');
        var phone = $(this).data('phone');
        var username = $(this).data('username');
        
        $('#editWorkerId').val(id);
        $('#editWorkerName').val(name);
        $('#editWorkerPhone').val(phone);
        $('#editWorkerUsername').val(username);
        $('#editWorkerPassword').val('');
        
        $('#editWorkerModal').modal('show');
    });

    $('#editWorkerForm').on('submit', function(e) {
        e.preventDefault();
        var form = $(this);
        var btn = form.find('button[type="submit"]');
        var id = $('#editWorkerId').val();
        
        btn.prop('disabled', true);
        
        $.ajax({
            url: '{{ url("station/workers") }}/' + id,
            method: 'PUT',
            data: form.serialize(),
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            success: function(res) {
                if (res.success) {
                    $('#editWorkerModal').modal('hide');
                    workersTable.ajax.reload();
                    Swal.fire({
                        icon: 'success',
                        title: '@lang("layouts.success")',
                        text: res.message,
                        timer: 2000,
                        showConfirmButton: false
                    });
                }
            },
            error: function(xhr) {
                var errors = xhr.responseJSON?.errors;
                if (errors) {
                    var errorHtml = Object.values(errors).flat().join('<br>');
                    Swal.fire({
                        icon: 'error',
                        title: '@lang("layouts.error")',
                        html: errorHtml
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: '@lang("layouts.error")',
                        text: xhr.responseJSON?.message || '@lang("station.workers.update_error")'
                    });
                }
            },
            complete: function() {
                btn.prop('disabled', false);
            }
        });
    });

    $(document).on('click', '.btn-toggle', function() {
        var btn = $(this);
        var id = btn.data('id');
        var isActive = btn.data('active');
        var action = isActive ? '@lang("station.workers.deactivate")' : '@lang("station.workers.activate")';
        
        Swal.fire({
            title: '@lang("station.workers.confirm_toggle")',
            text: action + '?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#e65100',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '@lang("layouts.yes")',
            cancelButtonText: '@lang("layouts.no")'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '{{ url("station/workers") }}/' + id + '/toggle',
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    success: function(res) {
                        if (res.success) {
                            workersTable.ajax.reload();
                            Swal.fire({
                                icon: 'success',
                                title: '@lang("layouts.success")',
                                text: res.message,
                                timer: 2000,
                                showConfirmButton: false
                            });
                        }
                    },
                    error: function(xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: '@lang("layouts.error")',
                            text: xhr.responseJSON?.message || '@lang("station.workers.toggle_error")'
                        });
                    }
                });
            }
        });
    });

    $(document).on('click', '.btn-delete', function() {
        var id = $(this).data('id');
        
        Swal.fire({
            title: '@lang("station.workers.confirm_delete")',
            text: '@lang("station.workers.delete_warning")',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '@lang("layouts.yes")',
            cancelButtonText: '@lang("layouts.no")'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '{{ url("station/workers") }}/' + id,
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    success: function(res) {
                        if (res.success) {
                            workersTable.ajax.reload();
                            Swal.fire({
                                icon: 'success',
                                title: '@lang("layouts.success")',
                                text: res.message,
                                timer: 2000,
                                showConfirmButton: false
                            });
                        }
                    },
                    error: function(xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: '@lang("layouts.error")',
                            text: xhr.responseJSON?.message || '@lang("station.workers.delete_error")'
                        });
                    }
                });
            }
        });
    });

    $(document).on('click', '.btn-quick-view', function() {
        var id = $(this).data('id');
        
        $.ajax({
            url: '{{ url("station/workers") }}/' + id,
            method: 'GET',
            success: function(res) {
                if (res.success) {
                    var data = res.data;
                    $('#qvWorkerName').text(data.name);
                    $('#qvWorkerUsername').text(data.username || '-');
                    $('#qvWorkerPhone').text(data.phone || '-');
                    $('#qvWorkerStatus').html(data.is_active 
                        ? '<span class="badge bg-success">@lang("station.workers.active")</span>' 
                        : '<span class="badge bg-danger">@lang("station.workers.inactive")</span>'
                    );
                    
                    $('#qvTodayTransactions').text(data.stats.today.transactions);
                    $('#qvTodayLiters').text(parseFloat(data.stats.today.liters).toFixed(2) + ' L');
                    $('#qvTodayAmount').text(parseFloat(data.stats.today.amount).toFixed(2) + ' @lang("station.currency")');
                    
                    $('#qvTotalTransactions').text(data.stats.total.transactions);
                    $('#qvTotalLiters').text(parseFloat(data.stats.total.liters).toFixed(2) + ' L');
                    $('#qvTotalAmount').text(parseFloat(data.stats.total.amount).toFixed(2) + ' @lang("station.currency")');
                    
                    $('#quickViewModal').modal('show');
                }
            },
            error: function() {
                Swal.fire({
                    icon: 'error',
                    title: '@lang("layouts.error")',
                    text: '@lang("station.workers.not_found")'
                });
            }
        });
    });
});
</script>
@endpush
