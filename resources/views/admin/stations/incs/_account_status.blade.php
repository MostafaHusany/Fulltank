<div class="text-center">
    @if($permissions == 'admin' || in_array('stations_edit', $permissions ?? []))
    <div class="form-check form-switch d-inline-block">
        <input class="form-check-input station-account-toggle" type="checkbox" role="switch" data-id="{{ $row_object->id }}"
            {{ $row_object->user && $row_object->user->is_active ? 'checked' : '' }}
            {{ !$row_object->user_id ? 'disabled' : '' }}>
        <label class="form-check-label small">
            {{ $row_object->user && $row_object->user->is_active ? __('layouts.active') : __('layouts.de-active') }}
        </label>
    </div>
    @else
    <span class="badge {{ $row_object->user && $row_object->user->is_active ? 'bg-success' : 'bg-warning' }}">
        {{ $row_object->user && $row_object->user->is_active ? __('layouts.active') : __('layouts.de-active') }}
    </span>
    @endif
</div>
