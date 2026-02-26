<div class="offcanvas offcanvas-end" tabindex="-1" id="walletTransactionsOffcanvas" aria-labelledby="walletTransactionsOffcanvasLabel" style="width: 520px;">
    <div class="offcanvas-header border-bottom">
        <h5 class="offcanvas-title" id="walletTransactionsOffcanvasLabel">@lang('wallets.Transaction History')</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body p-0">
        <div class="p-3 border-bottom">
            <span class="text-muted small" id="walletTransactionsCountText">0 @lang('wallets.Transaction History')</span>
        </div>
        <div class="p-3" style="max-height: 70vh; overflow-y: auto;">
            <div id="walletTransactionsLoading" class="text-center py-5" style="display: none;"><div class="spinner-border text-primary" role="status"></div></div>
            <div class="table-responsive">
                <table class="table table-sm table-hover mb-0" id="walletTransactionsTable">
                    <thead>
                        <tr>
                            <th>@lang('wallets.Date')</th>
                            <th>@lang('wallets.Type')</th>
                            <th>@lang('wallets.Amount')</th>
                            <th>@lang('wallets.Performer')</th>
                            <th>@lang('wallets.Before')</th>
                            <th>@lang('wallets.After')</th>
                            <th>@lang('wallets.Notes')</th>
                        </tr>
                    </thead>
                    <tbody id="walletTransactionsTableBody"></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@push('custome-js')
<script>
(function () {
    var ROUTES = {
        transactions: "{{ route('admin.wallets.transactions', ['walletId' => 'WID']) }}",
        toggleStatus: "{{ route('admin.wallets.toggleStatus', ['walletId' => 'WID']) }}"
    };
    var LANG = {
        transactionHistory: '{{ __("wallets.Transaction History") }}',
        client: '{{ __("wallets.Client") }}',
        object_error: '{{ __("wallets.object_error") }}',
        deposit: '{{ __("wallets.deposit") }}',
        withdrawal: '{{ __("wallets.withdrawal") }}',
        transfer: '{{ __("wallets.transfer") }}'
    };

    function loadWalletTransactions(walletId, clientName) {
        $('#walletTransactionsOffcanvasLabel').text(clientName + ' — ' + LANG.transactionHistory);
        $('#walletTransactionsLoading').show();
        $('#walletTransactionsTableBody').empty();
        var url = ROUTES.transactions.replace('WID', walletId);
        axios.get(url).then(function (res) {
            var data = res.data.data || [];
            $('#walletTransactionsCountText').text(data.length + ' ' + LANG.transactionHistory);
            var typeMap = { deposit: LANG.deposit, withdrawal: LANG.withdrawal, transfer: LANG.transfer };
            data.forEach(function (t) {
                var typeLabel = typeMap[t.type] || t.type;
                var row = '<tr><td>' + (t.created_at || '') + '</td><td>' + typeLabel + '</td><td>' + t.amount + '</td><td>' + (t.performer_name || '---') + '</td><td>' + t.before_balance + '</td><td>' + t.after_balance + '</td><td>' + (t.notes || '') + '</td></tr>';
                $('#walletTransactionsTableBody').append(row);
            });
        }).catch(function () {
            $('#walletTransactionsCountText').text(LANG.object_error);
        }).finally(function () {
            $('#walletTransactionsLoading').hide();
        });
    }

    $('document').ready(function () {
        $(document).on('click', '.wallet-history-btn', function () {
            var walletId = $(this).data('wallet-id');
            var clientName = $(this).data('client-name') || LANG.client;
            var offcanvas = new bootstrap.Offcanvas(document.getElementById('walletTransactionsOffcanvas'));
            offcanvas.show();
            loadWalletTransactions(walletId, clientName);
        });

        $(document).on('change', '.wallet-status-toggle', function () {
            var walletId = $(this).data('wallet-id');
            var $toggle = $(this);
            var url = ROUTES.toggleStatus.replace('WID', walletId);
            axios.put(url, {
                _token: $('meta[name="csrf-token"]').attr('content')
            }).then(function (res) {
                if (res.data.success) {
                    if (typeof successToast === 'function') successToast(res.data.msg);
                    if (window.walletDataTable && window.walletDataTable.table_object) window.walletDataTable.table_object.draw();
                } else {
                    $toggle.prop('checked', !$toggle.prop('checked'));
                    if (typeof failerToast === 'function') failerToast(res.data.msg || 'Error');
                }
            }).catch(function (err) {
                $toggle.prop('checked', !$toggle.prop('checked'));
                var msg = err.response && err.response.data && err.response.data.msg;
                if (typeof failerToast === 'function') failerToast(Array.isArray(msg) ? (msg[0] || 'Error') : (msg || 'Error'));
            });
        });
    });
})();
</script>
@endpush
