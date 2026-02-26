@extends('layouts.admin.app')

@push('title')
    <h1 class="h2">@lang('deposit_requests.Title Administration')</h1>
@endpush

@section('content')
    <div id="analyticsCard" class="card mb-3">
        <div class="card-header py-2">
            <a class="text-decoration-none text-dark" data-bs-toggle="collapse" href="#analyticsCollapse">
                <i class="fas fa-chart-line me-1"></i> @lang('deposit_requests.Total Net Deposits') / @lang('deposit_requests.Total Fees Collected')
            </a>
        </div>
        <div id="analyticsCollapse" class="collapse show">
            <div class="card-body py-2">
                <div class="row g-2">
                    <div class="col-6 col-md-3">
                        <span class="text-muted small">@lang('deposit_requests.Total Net Deposits')</span>
                        <div id="analytics-net" class="fw-bold">—</div>
                    </div>
                    <div class="col-6 col-md-3">
                        <span class="text-muted small">@lang('deposit_requests.Total Fees Collected')</span>
                        <div id="analytics-fees" class="fw-bold">—</div>
                    </div>
                    <div class="col-12" id="analytics-per-method"></div>
                </div>
            </div>
        </div>
    </div>

    <div id="objectsCard" class="card">
        <div class="card-header">
            <div class="row g-2 align-items-center">
                <div class="col-12 col-md-6 pt-1 order-2 order-md-1">
                    <span class="fw-bold">@lang('deposit_requests.Title Administration')</span>
                </div>
                <div class="col-12 col-md-6 order-1 order-md-2">
                    <div class="d-flex flex-wrap gap-1 justify-content-end">
                        <button class="btn btn-sm btn-outline-dark toggle-search" title="@lang('layouts.show')">
                            <i class="fas fa-search"></i>
                        </button>
                        <button class="relode-btn btn btn-sm btn-outline-dark" title="@lang('clients.object_updated')">
                            <i class="relode-btn-icon fas fa-sync-alt"></i>
                            <span class="relode-btn-loader spinner-grow spinner-grow-sm" style="display: none;" role="status" aria-hidden="true"></span>
                        </button>
                        @if($permissions == 'admin' || in_array('depositRequests_add', $permissions ?? []))
                        <button class="btn btn-sm btn-outline-primary toggle-btn" data-current-card="#objectsCard" data-target-card="#createObjectCard" title="@lang('deposit_requests.Create Request')">
                            <i class="fas fa-plus"></i>
                        </button>
                        @endif
                        <a href="{{ route('admin.financialSettings.index') }}" class="btn btn-sm btn-outline-secondary"><i class="fas fa-cog"></i> @lang('deposit_requests.Financial Settings')</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="card-body custome-table p-2 p-md-3">
            @include('admin.deposit_requests.incs._search')

            <div class="table-responsive">
                <table id="dataTable" class="table table-sm table-hover text-center mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>@lang('deposit_requests.Ref Number')</th>
                            <th>@lang('deposit_requests.Request Date')</th>
                            <th>@lang('deposit_requests.Client')</th>
                            <th>@lang('deposit_requests.Amount')</th>
                            <th>@lang('deposit_requests.Fee')</th>
                            <th>@lang('deposit_requests.Total to Pay')</th>
                            <th>@lang('deposit_requests.Payment Method')</th>
                            <th>@lang('deposit_requests.Proof Image')</th>
                            <th>@lang('deposit_requests.Status')</th>
                            <th>@lang('deposit_requests.Reviewer')</th>
                            <th>@lang('deposit_requests.Processor')</th>
                            <th>@lang('layouts.Actions')</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div><!-- /.card -->

    @if($permissions == 'admin' || in_array('depositRequests_add', $permissions ?? []))
        @include('admin.deposit_requests.incs._create')
    @endif
    
    @if($permissions == 'admin' || in_array('depositRequests_show', $permissions ?? []) || in_array('depositRequests_edit', $permissions ?? []))
        @include('admin.deposit_requests.incs._proof_img_modal')
        @include('admin.deposit_requests.incs._generated_record_modal')
    @endif

@endSection

@push('custome-js')
<script>
    (function () {
        const ROUTES = {
            index          : "{{ route('admin.depositRequests.index') }}",
            generatedRecord: "{{ route('admin.depositRequests.generatedRecord', ['id' => 'ID']) }}",
            analytics      : "{{ route('admin.depositRequests.analytics') }}",
            store          : "{{ route('admin.depositRequests.store') }}",
            update         : "{{ route('admin.depositRequests.update', ['id' => 'ID']) }}",
            generateBalance: "{{ route('admin.depositRequests.generateBalance', ['id' => 'ID']) }}",
            calculateFee  : "{{ route('admin.depositRequests.calculateFee') }}",
            clients       : "{{ route('admin.search.clients') }}",
            paymentMethods: "{{ route('admin.paymentMethods.list') }}"
        };

        const LANG = {
            selectClient        : '{{ __("deposit_requests.Client") }}',
            selectPaymentMethod : '{{ __("deposit_requests.Payment Method") }}',
            proof_required      : '{{ __("deposit_requests.proof_required") }}',
            confirmGenerate     : '{{ __("deposit_requests.Confirm Generate") }}',
            confirmApprove      : '{{ __("deposit_requests.Confirm Approve") }}',
            confirmReject       : '{{ __("deposit_requests.Confirm Reject") }}',
            amount_required     : '{{ __("deposit_requests.Amount") }}',
            client_required     : '{{ __("deposit_requests.Client") }}',
            paymentMethod_required: '{{ __("deposit_requests.Payment Method") }}'
        };

        const VALIDATION = {
            client_id         : LANG.client_required,
            amount            : LANG.amount_required,
            payment_method_id : LANG.paymentMethod_required,
            proof_image       : LANG.proof_required
        };

        $('document').ready(function () {

            const objects_dynamic_table = new DynamicTable(
                {
                    index_route   : ROUTES.index,
                    store_route   : ROUTES.store,
                    show_route    : ROUTES.index,
                    update_route  : ROUTES.index,
                    destroy_route : ROUTES.index,
                    draft         : { route: '', flag: '' }
                },
                '#dataTable',
                { success_el: '#successAlert', danger_el: '#dangerAlert', warning_el: '#warningAlert' },
                {
                    table_id        : '#dataTable',
                    toggle_btn      : '.toggle-btn',
                    create_obj_btn  : '.create-object',
                    update_obj_btn  : '.update-object',
                    draft_obj_btn   : '.create-draft',
                    fields_list     : ['client_id', 'amount', 'payment_method_id', 'proof_image'],
                    imgs_fields     : ['proof_image']
                },
                [
                    { data: 'id',                  name: 'id' },
                    { data: 'ref_number',          name: 'ref_number' },
                    { data: 'request_date',        name: 'request_date' },
                    { data: 'client_name',         name: 'client_name' },
                    { data: 'amount',              name: 'amount' },
                    { data: 'fee_amount',          name: 'fee_amount' },
                    { data: 'total_to_pay',        name: 'total_to_pay' },
                    { data: 'payment_method_name', name: 'payment_method_name' },
                    { data: 'proof_thumb',         name: 'proof_thumb' },
                    { data: 'status_badge',        name: 'status_badge' },
                    { data: 'reviewer_name',       name: 'reviewer_name' },
                    { data: 'processor_name',      name: 'processor_name' },
                    { data: 'actions',             name: 'actions' }
                ],
                function (d) {
                    if ($('#s-ref_number').length) d.ref_number = $('#s-ref_number').val();
                    if ($('#s-client_id').length) d.client_id = $('#s-client_id').val();
                    if ($('#s-status').length) d.status = $('#s-status').val();
                    if ($('#s-start_date').length) d.start_date = $('#s-start_date').val();
                    if ($('#s-end_date').length) d.end_date = $('#s-end_date').val();
                }
            );

            objects_dynamic_table.validateData = (data, prefix) => {
                var valid = true;
                $('.err-msg').slideUp(500);
                Object.keys(VALIDATION).forEach(function (field) {
                    var val = data.get(field);
                    if (!val || (typeof val === 'string' && val === '')) {
                        valid = false;
                        var errEl = $('#' + (prefix || '') + field + 'Err');
                        if (errEl.length) errEl.text(VALIDATION[field]).slideDown(500);
                    }
                });
                return valid;
            };

            var baseClearForm = objects_dynamic_table.clearForm;
            objects_dynamic_table.clearForm = function (fields_list) {
                baseClearForm.call(this, fields_list);
                $('#fee_display, #total_display').val('');
            };

            window.depositRequestsTable = objects_dynamic_table;

            function loadAnalytics() {
                axios.get(ROUTES.analytics).then(function (r) {
                    if (r.data.success && r.data.data) {
                        var d = r.data.data;
                        $('#analytics-net').text((d.total_net_deposits || 0) + ' EGP');
                        $('#analytics-fees').text((d.total_fees_collected || 0) + ' EGP');
                        var perMethod = d.per_payment_method || [];
                        var html = perMethod.length ? '<table class="table table-sm mb-0 mt-2"><thead><tr><th>@lang("deposit_requests.Payment Method")</th><th>@lang("deposit_requests.Total Net Deposits")</th><th>@lang("deposit_requests.Total Fees Collected")</th></tr></thead><tbody>' + perMethod.map(function (m) { return '<tr><td>' + (m.payment_method && m.payment_method.name ? m.payment_method.name : '—') + '</td><td>' + (m.total_amount || 0) + ' EGP</td><td>' + (m.total_fees || 0) + ' EGP</td></tr>'; }).join('') + '</tbody></table>' : '';
                        $('#analytics-per-method').html(html);
                    }
                });
            }

            loadAnalytics();

            objects_dynamic_table.table_object.on('draw.dt', function () {
                loadAnalytics();
            });

            $('.relode-btn').on('click', function () {
                objects_dynamic_table.table_object.draw();
            });

            
            (function initDepositStatusActions() {
                $(document).on('click', '.deposit-approve-btn', function (e) {
                    e.preventDefault();
                    var id = $(this).data('id');
                    if (!confirm(LANG.confirmApprove)) return;
                    axios.put(ROUTES.update.replace('ID', id), { _token: $('meta[name="csrf-token"]').attr('content'), status: 'approved' }).then(function (r) {
                        if (r.data.success) { successToast(r.data.msg); objects_dynamic_table.table_object.draw(); }
                        else { failerToast(r.data.msg || 'Error'); }
                    }).catch(function (e) { failerToast((e.response && e.response.data && e.response.data.msg && e.response.data.msg[0]) || 'Error'); });
                });

                $(document).on('click', '.deposit-reject-btn', function (e) {
                    e.preventDefault();
                    var id = $(this).data('id');
                    if (!confirm(LANG.confirmReject)) return;
                    axios.put(ROUTES.update.replace('ID', id), { _token: $('meta[name="csrf-token"]').attr('content'), status: 'rejected' }).then(function (r) {
                        if (r.data.success) { successToast(r.data.msg); objects_dynamic_table.table_object.draw(); }
                        else { failerToast(r.data.msg || 'Error'); }
                    }).catch(function (e) { failerToast((e.response && e.response.data && e.response.data.msg && e.response.data.msg[0]) || 'Error'); });
                });

                $(document).on('click', '.deposit-generate-btn', function () {
                    var id = $(this).data('id');
                    if (!confirm(LANG.confirmGenerate)) return;
                    axios.post(ROUTES.generateBalance.replace('ID', id), { _token: $('meta[name="csrf-token"]').attr('content') }).then(function (r) {
                        if (r.data.success) { successToast(r.data.msg); objects_dynamic_table.table_object.draw(); }
                        else { failerToast(r.data.msg || 'Error'); }
                    }).catch(function (e) { failerToast((e.response && e.response.data && e.response.data.msg && e.response.data.msg[0]) || 'Error'); });
                });

                $(document).on('click', '.deposit-show-generated-btn', function () {
                    var id = $(this).data('id');
                    axios.get(ROUTES.generatedRecord.replace('ID', id)).then(function (r) {
                        if (r.data.success && r.data.data) {
                            var d = r.data.data;
                            $('#generatedRecordAmount').text((d.amount || 0) + ' EGP');
                            $('#generatedRecordDate').text(d.created_at || '—');
                            $('#generatedRecordNotes').text(d.notes || '').toggle(!!d.notes);
                            new bootstrap.Modal(document.getElementById('generatedRecordModal')).show();
                        } else {
                            failerToast(r.data.msg || 'Error');
                        }
                    }).catch(function (e) { failerToast((e.response && e.response.data && e.response.data.msg && e.response.data.msg[0]) || 'Error'); });
                });
            })();

            axios.get(ROUTES.clients, { params: { q: '' } }).then(function (res) {
                var data = res.data || [];
                var $sel = $('#s-client_id');
                if ($sel.length) {
                    $sel.find('option:not(:first)').remove();
                    data.forEach(function (item) {
                        $sel.append(new Option((item.company_name || item.name) + (item.phone ? ' - ' + item.phone : ''), item.id));
                    });
                }
            });

            (function initDepositCreateForm() {
                if (!$('#client_id').length) return;

                var clientsSelect2Opts = {
                    allowClear: true,
                    width: '100%',
                    placeholder: LANG.selectClient,
                    dropdownParent: $('body'),
                    ajax: {
                        url: ROUTES.clients,
                        dataType: 'json',
                        delay: 250,
                        data: function (params) { return { q: params.term || '' }; },
                        processResults: function (data) {
                            var arr = Array.isArray(data) ? data : (data && data.results ? data.results : []);
                            return {
                                results: arr.map(function (item) {
                                    return { id: item.id, text: (item.company_name || item.name) + (item.phone ? ' - ' + item.phone : '') };
                                })
                            };
                        },
                        cache: true
                    },
                    minimumInputLength: 0
                };

                $('#client_id').select2(clientsSelect2Opts);

                axios.get(ROUTES.paymentMethods).then(function (r) {
                    var data = r.data.data || [];
                    var $sel = $('#payment_method_id');
                    $sel.empty().append('<option value="">-- ' + LANG.selectPaymentMethod + ' --</option>');
                    data.forEach(function (i) {
                        $sel.append(new Option(i.name + (i.account_details ? ' (' + i.account_details + ')' : ''), i.id));
                    });
                });

                $('#amount').on('input', function () {
                    var amt = parseFloat($(this).val()) || 0;
                    if (amt <= 0) {
                        $('#fee_display').val('');
                        $('#total_display').val('');
                        return;
                    }
                    axios.get(ROUTES.calculateFee, { params: { amount: amt } }).then(function (r) {
                        if (r.data.success && r.data.data) {
                            $('#fee_display').val(r.data.data.fee_amount);
                            $('#total_display').val(r.data.data.total_to_pay);
                        }
                    });
                });
            })();

        });
    })();
</script>
@endpush
