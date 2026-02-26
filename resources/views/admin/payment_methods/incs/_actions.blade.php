<div class="d-flex gap-1">
    @if($permissions == 'admin' || in_array('paymentMethods_edit', $permissions ?? []))
    <button type="button" class="btn btn-sm btn-outline-warning pm-edit-btn" data-id="{{ $row->id }}" data-name="{{ e($row->name) }}" data-account-details="{{ e($row->account_details ?? '') }}" data-is-active="{{ $row->is_active ? '1' : '0' }}">@lang('layouts.edit')</button>
    @endif
    @if($permissions == 'admin' || in_array('paymentMethods_delete', $permissions ?? []))
    <button type="button" class="btn btn-sm btn-outline-danger pm-delete-btn" data-id="{{ $row->id }}">@lang('layouts.delete')</button>
    @endif
</div>
