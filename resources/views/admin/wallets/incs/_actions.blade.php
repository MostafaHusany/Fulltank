@php
    $clientName = $row_object->user ? e($row_object->user->company_name ?: $row_object->user->name) : '---';
@endphp
<div class="d-flex flex-wrap gap-1 justify-content-center">
    @if($permissions == 'admin' || in_array('wallets_add', $permissions))
    <button type="button" class="wallet-add-balance-btn btn btn-sm btn-outline-primary"
        data-wallet-id="{{ $row_object->id }}"
        data-client-name="{{ $clientName }}">
        <i class="fas fa-plus"></i> @lang('wallets.Add Balance')
    </button>
    @endif
    @if($permissions == 'admin' || in_array('wallets_show', $permissions))
    <button type="button" class="wallet-history-btn btn btn-sm btn-outline-info"
        data-wallet-id="{{ $row_object->id }}"
        data-client-name="{{ $clientName }}">
        <i class="fas fa-history"></i> @lang('wallets.Transaction History')
    </button>
    @endif
</div>
