<div class="d-flex justify-content-center">
    @if($permissions == 'admin' || in_array('wallets_edit', $permissions))
    <div class="form-check form-switch">
        <input class="wallet-status-toggle form-check-input" id="walletSwitch{{ $row_object->id }}" data-wallet-id="{{ $row_object->id }}" type="checkbox" @if($row_object->is_active) checked @endif>
        <label class="form-check-label" for="walletSwitch{{ $row_object->id }}"></label>
    </div>
    @else
    {!! $row_object->is_active ? '<span class="badge bg-success">' . __('layouts.active') . '</span>' : '<span class="badge bg-warning">' . __('layouts.de-active') . '</span>' !!}
    @endif
</div>
