@php
    $stationName = $row_object->station ? e($row_object->station->name) : '---';
@endphp
<div class="d-flex flex-wrap gap-1 justify-content-center">
    @if($permissions == 'admin' || in_array('stationWallets_show', $permissions))
    <button type="button" class="station-wallet-history-btn btn btn-sm btn-outline-info"
        data-wallet-id="{{ $row_object->id }}"
        data-station-name="{{ $stationName }}">
        <i class="fas fa-history"></i> @lang('station_wallets.Transaction History')
    </button>
    @endif
</div>
