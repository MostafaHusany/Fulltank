<div class="d-flex justify-content-center">
    @if($permissions == 'admin' || in_array('stationWallets_edit', $permissions))
    <div class="form-check form-switch">
        <input class="station-wallet-status-toggle form-check-input" id="stationWalletSwitch{{ $row_object->id }}" data-wallet-id="{{ $row_object->id }}" type="checkbox" @if($row_object->is_active) checked @endif>
        <label class="form-check-label" for="stationWalletSwitch{{ $row_object->id }}"></label>
    </div>
    @else
    @if($row_object->is_active)
        <span class="badge bg-success">@lang('layouts.active')</span>
    @else
        <span class="badge bg-warning">@lang('layouts.de-active')</span>
    @endif
    @endif
</div>
