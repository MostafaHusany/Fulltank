@extends('layouts.clients.app')

@push('title')
    <h1 class="h2">@lang('client.deposits.title')</h1>
@endpush

@push('custome-plugin')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        .balance-card {
            border-radius: 16px;
            background: linear-gradient(135deg, #198754 0%, #20c997 100%);
            border: none;
        }
        .balance-card .balance-value {
            font-size: 2rem;
            font-weight: 700;
        }
        .request-card {
            border-radius: 16px;
            border: none;
        }
        .payment-method-card {
            border: 2px solid #dee2e6;
            border-radius: 12px;
            padding: 1rem;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        .payment-method-card:hover {
            border-color: #0d6efd;
            background: #f8f9fa;
        }
        .payment-method-card.selected {
            border-color: #0d6efd;
            background: #e7f1ff;
        }
        .payment-method-card input[type="radio"] {
            display: none;
        }
        .proof-preview {
            max-height: 150px;
            border-radius: 8px;
            object-fit: cover;
        }
        .fee-summary {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 1rem;
        }
    </style>
@endpush

@section('content')
<div class="container-fluid px-0">

    {{-- Balance Card + Add Button --}}
    <div class="row mb-4 g-3">
        <div class="col-12 col-md-6">
            <div class="card balance-card shadow-sm h-100">
                <div class="card-body text-white d-flex align-items-center">
                    <div class="me-3">
                        <div class="bg-white bg-opacity-25 rounded-3 p-3">
                            <i class="fas fa-wallet fa-2x text-white"></i>
                        </div>
                    </div>
                    <div>
                        <p class="text-white-50 mb-1">@lang('client.deposits.current_balance')</p>
                        <h2 class="balance-value mb-0">{{ number_format($balance, 2) }}</h2>
                        <small class="text-white-50">@lang('client.currency')</small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-6">
            <div class="card request-card shadow-sm h-100">
                <div class="card-body d-flex flex-column justify-content-center align-items-center text-center">
                    <i class="fas fa-plus-circle fa-3x text-primary mb-3"></i>
                    <h5 class="mb-2">@lang('client.deposits.new_request')</h5>
                    <p class="text-muted small mb-3">@lang('client.deposits.new_request_desc')</p>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addDepositModal">
                        <i class="fas fa-plus me-2"></i>@lang('client.deposits.add_request')
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Deposits Table --}}
    <div class="card request-card shadow-sm">
        <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="fas fa-history me-2 text-primary"></i>@lang('client.deposits.request_history')
            </h5>
            <button type="button" class="btn btn-outline-primary btn-sm" id="refreshTableBtn">
                <i class="fas fa-sync-alt me-1"></i>@lang('client.deposits.refresh')
            </button>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="deposits-table" class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>@lang('client.deposits.ref_number')</th>
                            <th>@lang('client.deposits.date')</th>
                            <th>@lang('client.deposits.amount')</th>
                            <th>@lang('client.deposits.fee')</th>
                            <th>@lang('client.deposits.total')</th>
                            <th>@lang('client.deposits.payment_method')</th>
                            <th>@lang('client.deposits.status')</th>
                            <th>@lang('layouts.Actions')</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>

</div>

{{-- Add Deposit Modal --}}
<div class="modal fade" id="addDepositModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content" style="border-radius: 16px; border: none;">
            <form id="addDepositForm" enctype="multipart/form-data">
                @csrf
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title">
                        <i class="fas fa-plus-circle me-2 text-primary"></i>@lang('client.deposits.add_request')
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        {{-- Amount --}}
                        <div class="col-12 mb-3">
                            <label class="form-label fw-semibold">@lang('client.deposits.amount') <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="number" class="form-control form-control-lg" name="amount" id="depositAmount" 
                                    min="1" step="0.01" placeholder="0.00" required>
                                <span class="input-group-text">@lang('client.currency')</span>
                            </div>
                        </div>

                        {{-- Fee Summary --}}
                        <div class="col-12 mb-3">
                            <div class="fee-summary" id="feeSummary" style="display: none;">
                                <div class="row text-center">
                                    <div class="col-4">
                                        <small class="text-muted d-block">@lang('client.deposits.amount')</small>
                                        <span class="fw-bold" id="feeAmount">0.00</span>
                                    </div>
                                    <div class="col-4">
                                        <small class="text-muted d-block">@lang('client.deposits.fee')</small>
                                        <span class="fw-bold text-warning" id="feeFee">0.00</span>
                                    </div>
                                    <div class="col-4">
                                        <small class="text-muted d-block">@lang('client.deposits.total_to_pay')</small>
                                        <span class="fw-bold text-success" id="feeTotal">0.00</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Payment Method --}}
                        <div class="col-12 mb-3">
                            <label class="form-label fw-semibold">@lang('client.deposits.payment_method') <span class="text-danger">*</span></label>
                            <div class="row g-2" id="paymentMethodsContainer">
                                @foreach($paymentMethods as $method)
                                <div class="col-6 col-md-4">
                                    <label class="payment-method-card d-block text-center">
                                        <input type="radio" name="payment_method_id" value="{{ $method->id }}">
                                        <i class="fas fa-credit-card fa-2x mb-2 text-primary"></i>
                                        <div class="fw-semibold">{{ $method->name }}</div>
                                        @if($method->account_details)
                                        <small class="text-muted d-block">{{ Str::limit($method->account_details, 30) }}</small>
                                        @endif
                                    </label>
                                </div>
                                @endforeach
                            </div>
                            <div class="invalid-feedback" id="paymentMethodError" style="display: none;">
                                @lang('client.deposits.payment_method_required')
                            </div>
                        </div>

                        {{-- Proof Image --}}
                        <div class="col-12 mb-3">
                            <label class="form-label fw-semibold">@lang('client.deposits.proof_image') <span class="text-danger">*</span></label>
                            <input type="file" class="form-control" name="proof_image" id="proofImage" accept="image/*" required>
                            <small class="text-muted">@lang('client.deposits.proof_hint')</small>
                            <div class="mt-2" id="proofPreviewContainer" style="display: none;">
                                <img src="" alt="Preview" class="proof-preview" id="proofPreview">
                            </div>
                        </div>

                        {{-- Notes --}}
                        <div class="col-12 mb-3">
                            <label class="form-label fw-semibold">@lang('client.deposits.notes')</label>
                            <textarea class="form-control" name="notes" rows="2" placeholder="@lang('client.deposits.notes_placeholder')"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">@lang('layouts.close')</button>
                    <button type="submit" class="btn btn-primary" id="submitDepositBtn">
                        <i class="fas fa-paper-plane me-2"></i>@lang('client.deposits.submit_request')
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- View Proof Modal --}}
<div class="modal fade" id="viewProofModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: 16px; border: none;">
            <div class="modal-header border-0">
                <h5 class="modal-title">
                    <i class="fas fa-image me-2 text-primary"></i>@lang('client.deposits.proof_image')
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <img src="" alt="Proof" class="img-fluid rounded" id="viewProofImage" style="max-height: 400px;">
            </div>
        </div>
    </div>
</div>
@endsection

@push('custome-js')
<script>
(function() {
    var isAr = {{ app()->getLocale() === 'ar' ? 'true' : 'false' }};
    var currency = '@lang('client.currency')';

    var LANG = {
        viewProof: '@lang('client.deposits.view_proof')',
        cancel: '@lang('client.deposits.cancel')',
        confirmCancel: '@lang('client.deposits.confirm_cancel')',
        yes: '@lang('layouts.yes')',
        no: '@lang('layouts.no')',
        success: '@lang('layouts.success')',
        error: '@lang('layouts.error')'
    };

    var depositsTable = $('#deposits-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: '{{ route("client.deposits.index") }}',
        columns: [
            { data: 'ref_number', name: 'ref_number' },
            { data: 'formatted_date', name: 'created_at' },
            { 
                data: 'amount_display', 
                name: 'amount',
                render: function(data) {
                    return '<span class="fw-bold">' + data + '</span>';
                }
            },
            { data: 'fee_display', name: 'fee_amount' },
            { 
                data: 'total_display', 
                name: 'total_to_pay',
                render: function(data) {
                    return '<span class="fw-bold text-success">' + data + '</span>';
                }
            },
            { data: 'payment_method_name', name: 'payment_method_name', orderable: false },
            { data: 'status_badge', name: 'status', orderable: false },
            {
                data: null,
                orderable: false,
                render: function(data, type, row) {
                    var html = '<div class="btn-group btn-group-sm">';
                    if (row.has_proof) {
                        html += '<button class="btn btn-outline-primary" onclick="viewProof(' + row.id + ')" title="' + LANG.viewProof + '">';
                        html += '<i class="fas fa-image"></i></button>';
                    }
                    if (row.can_cancel) {
                        html += '<button class="btn btn-outline-danger" onclick="cancelDeposit(' + row.id + ')" title="' + LANG.cancel + '">';
                        html += '<i class="fas fa-times"></i></button>';
                    }
                    html += '</div>';
                    return html;
                }
            }
        ],
        order: [[1, 'desc']],
        language: {
            url: isAr ? '//cdn.datatables.net/plug-ins/1.13.4/i18n/ar.json' : ''
        }
    });

    $('.payment-method-card').on('click', function() {
        $('.payment-method-card').removeClass('selected');
        $(this).addClass('selected');
        $('#paymentMethodError').hide();
        $('#paymentMethodsContainer').removeClass('border border-danger rounded p-2');
    });

    var feeTimeout;
    $('#depositAmount').on('input', function() {
        var amount = parseFloat($(this).val()) || 0;
        clearTimeout(feeTimeout);
        
        if (amount >= 1) {
            feeTimeout = setTimeout(function() {
                $.ajax({
                    url: '{{ route("client.deposits.calculateFee") }}',
                    data: { amount: amount },
                    success: function(res) {
                        if (res.status) {
                            $('#feeAmount').text(parseFloat(res.data.amount).toFixed(2));
                            $('#feeFee').text(parseFloat(res.data.fee_amount).toFixed(2));
                            $('#feeTotal').text(parseFloat(res.data.total_to_pay).toFixed(2));
                            $('#feeSummary').slideDown();
                        }
                    }
                });
            }, 500);
        } else {
            $('#feeSummary').slideUp();
        }
    });

    $('#proofImage').on('change', function() {
        var file = this.files[0];
        if (file) {
            var reader = new FileReader();
            reader.onload = function(e) {
                $('#proofPreview').attr('src', e.target.result);
                $('#proofPreviewContainer').slideDown();
            };
            reader.readAsDataURL(file);
        } else {
            $('#proofPreviewContainer').slideUp();
        }
    });

    $('#addDepositForm').on('submit', function(e) {
        e.preventDefault();
        
        var isValid = true;
        var errorMessages = [];

        var amount = parseFloat($('#depositAmount').val()) || 0;
        if (amount < 1) {
            isValid = false;
            errorMessages.push('@lang('client.deposits.amount_required')');
            $('#depositAmount').addClass('is-invalid');
        } else {
            $('#depositAmount').removeClass('is-invalid');
        }

        var paymentMethod = $('input[name="payment_method_id"]:checked').val();
        if (!paymentMethod) {
            isValid = false;
            errorMessages.push('@lang('client.deposits.payment_method_required')');
            $('#paymentMethodError').show();
            $('#paymentMethodsContainer').addClass('border border-danger rounded p-2');
        } else {
            $('#paymentMethodError').hide();
            $('#paymentMethodsContainer').removeClass('border border-danger rounded p-2');
        }

        var proofFile = $('#proofImage')[0].files[0];
        if (!proofFile) {
            isValid = false;
            errorMessages.push('@lang('client.deposits.proof_required')');
            $('#proofImage').addClass('is-invalid');
        } else {
            $('#proofImage').removeClass('is-invalid');
        }

        if (!isValid) {
            Swal.fire({
                icon: 'warning',
                title: '@lang('client.deposits.validation_error')',
                html: errorMessages.join('<br>'),
                confirmButtonColor: '#ffc107'
            });
            return;
        }
        
        var formData = new FormData(this);
        var btn = $('#submitDepositBtn');
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>@lang('client.deposits.submitting')');

        $.ajax({
            url: '{{ route("client.deposits.store") }}',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(res) {
                btn.prop('disabled', false).html('<i class="fas fa-paper-plane me-2"></i>@lang('client.deposits.submit_request')');
                
                if (res.data.status) {
                    $('#addDepositModal').modal('hide');
                    $('#addDepositForm')[0].reset();
                    $('#feeSummary').hide();
                    $('#proofPreviewContainer').hide();
                    $('.payment-method-card').removeClass('selected');
                    
                    Swal.fire({
                        icon: 'success',
                        title: LANG.success,
                        text: res.data.message,
                        confirmButtonColor: '#198754'
                    });
                    
                    depositsTable.ajax.reload();
                } else {
                    var errors = res.data.message;
                    var errorText = typeof errors === 'object' ? Object.values(errors).flat().join('\n') : errors;
                    Swal.fire({
                        icon: 'error',
                        title: LANG.error,
                        text: errorText
                    });
                }
            },
            error: function() {
                btn.prop('disabled', false).html('<i class="fas fa-paper-plane me-2"></i>@lang('client.deposits.submit_request')');
                Swal.fire({
                    icon: 'error',
                    title: LANG.error,
                    text: '@lang('client.deposits.error')'
                });
            }
        });
    });

    window.viewProof = function(id) {
        $('#viewProofImage').attr('src', '{{ url("client/deposits") }}/' + id + '/proof');
        var modal = new bootstrap.Modal(document.getElementById('viewProofModal'));
        modal.show();
    };

    window.cancelDeposit = function(id) {
        Swal.fire({
            title: LANG.confirmCancel,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: LANG.yes,
            cancelButtonText: LANG.no
        }).then(function(result) {
            if (result.isConfirmed) {
                $.ajax({
                    url: '{{ url("client/deposits") }}/' + id + '/cancel',
                    method: 'POST',
                    data: { _token: '{{ csrf_token() }}' },
                    success: function(res) {
                        if (res.status) {
                            Swal.fire({
                                icon: 'success',
                                title: LANG.success,
                                text: res.message,
                                confirmButtonColor: '#198754'
                            });
                            depositsTable.ajax.reload();
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: LANG.error,
                                text: res.message
                            });
                        }
                    }
                });
            }
        });
    };

    $('#refreshTableBtn').on('click', function() {
        var btn = $(this);
        btn.prop('disabled', true);
        btn.find('i').addClass('fa-spin');
        
        depositsTable.ajax.reload(function() {
            btn.prop('disabled', false);
            btn.find('i').removeClass('fa-spin');
        }, false);
    });

    $('#addDepositModal').on('hidden.bs.modal', function() {
        $('#addDepositForm')[0].reset();
        $('#feeSummary').hide();
        $('#proofPreviewContainer').hide();
        $('.payment-method-card').removeClass('selected');
    });
})();
</script>
@endpush
