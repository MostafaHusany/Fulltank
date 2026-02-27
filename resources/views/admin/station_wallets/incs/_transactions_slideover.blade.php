<div class="offcanvas offcanvas-end" tabindex="-1" id="stationWalletTransactionsOffcanvas" aria-labelledby="stationWalletTransactionsOffcanvasLabel" style="width: 520px;">
    <div class="offcanvas-header border-bottom">
        <h5 class="offcanvas-title" id="stationWalletTransactionsOffcanvasLabel">@lang('station_wallets.Transaction History')</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body p-0">
        <div class="p-3 border-bottom">
            <span class="text-muted small" id="stationWalletTransactionsCountText">0 @lang('station_wallets.transactions')</span>
        </div>
        <div class="p-3" style="max-height: 70vh; overflow-y: auto;">
            <div id="stationWalletTransactionsLoading" class="text-center py-5" style="display: none;">
                <div class="spinner-border text-primary" role="status"></div>
            </div>
            <div class="table-responsive">
                <table class="table table-sm table-hover mb-0" id="stationWalletTransactionsTable">
                    <thead>
                        <tr>
                            <th>@lang('station_wallets.Date')</th>
                            <th>@lang('station_wallets.Type')</th>
                            <th>@lang('station_wallets.Amount')</th>
                            <th>@lang('station_wallets.Performer')</th>
                            <th>@lang('station_wallets.Before')</th>
                            <th>@lang('station_wallets.After')</th>
                            <th>@lang('station_wallets.Notes')</th>
                        </tr>
                    </thead>
                    <tbody id="stationWalletTransactionsTableBody"></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@push('custome-js')
<script>
(function () {
    var ROUTES = {
        transactions: "{{ route('admin.stationWallets.transactions', ['walletId' => 'WID']) }}",
        toggleStatus: "{{ route('admin.stationWallets.toggleStatus', ['walletId' => 'WID']) }}"
    };
    var LANG = {
        transactionHistory: '@lang("station_wallets.Transaction History")',
        transactions: '@lang("station_wallets.transactions")',
        station: '@lang("station_wallets.Station Name")',
        object_error: '@lang("station_wallets.object_error")',
        deposit: '@lang("station_wallets.deposit")',
        withdrawal: '@lang("station_wallets.withdrawal")',
        transfer: '@lang("station_wallets.transfer")',
        fueling: '@lang("station_wallets.fueling")'
    };

    function loadStationWalletTransactions(walletId, stationName) {
        $('#stationWalletTransactionsOffcanvasLabel').text(stationName + ' — ' + LANG.transactionHistory);
        $('#stationWalletTransactionsLoading').show();
        $('#stationWalletTransactionsTableBody').empty();

        var url = ROUTES.transactions.replace('WID', walletId);
        axios.get(url).then(function (res) {
            var data = res.data.data || [];
            $('#stationWalletTransactionsCountText').text(data.length + ' ' + LANG.transactions);

            var typeMap = {
                deposit: LANG.deposit,
                withdrawal: LANG.withdrawal,
                transfer: LANG.transfer,
                fueling: LANG.fueling
            };

            data.forEach(function (t) {
                var typeLabel = typeMap[t.type] || t.type;
                var row = '<tr>' +
                    '<td>' + (t.created_at || '') + '</td>' +
                    '<td>' + typeLabel + '</td>' +
                    '<td>' + t.amount + '</td>' +
                    '<td>' + (t.performer_name || '---') + '</td>' +
                    '<td>' + t.before_balance + '</td>' +
                    '<td>' + t.after_balance + '</td>' +
                    '<td>' + (t.notes || '') + '</td>' +
                    '</tr>';
                $('#stationWalletTransactionsTableBody').append(row);
            });
        }).catch(function () {
            $('#stationWalletTransactionsCountText').text(LANG.object_error);
        }).finally(function () {
            $('#stationWalletTransactionsLoading').hide();
        });
    }

    $('document').ready(function () {
        $(document).on('click', '.station-wallet-history-btn', function () {
            var walletId = $(this).data('wallet-id');
            var stationName = $(this).data('station-name') || LANG.station;
            var offcanvas = new bootstrap.Offcanvas(document.getElementById('stationWalletTransactionsOffcanvas'));
            offcanvas.show();
            loadStationWalletTransactions(walletId, stationName);
        });

        $(document).on('change', '.station-wallet-status-toggle', function () {
            var walletId = $(this).data('wallet-id');
            var $toggle = $(this);
            var url = ROUTES.toggleStatus.replace('WID', walletId);

            axios.put(url, {
                _token: $('meta[name="csrf-token"]').attr('content')
            }).then(function (res) {
                if (res.data.success) {
                    if (typeof successToast === 'function') successToast(res.data.msg);
                    if (window.stationWalletDataTable && window.stationWalletDataTable.table_object) {
                        window.stationWalletDataTable.table_object.draw();
                    }
                } else {
                    $toggle.prop('checked', !$toggle.prop('checked'));
                    if (typeof failerToast === 'function') failerToast(res.data.msg || 'Error');
                }
            }).catch(function (err) {
                $toggle.prop('checked', !$toggle.prop('checked'));
                var msg = err.response && err.response.data && err.response.data.msg;
                if (typeof failerToast === 'function') {
                    failerToast(Array.isArray(msg) ? (msg[0] || 'Error') : (msg || 'Error'));
                }
            });
        });
    });
})();
</script>
@endpush
