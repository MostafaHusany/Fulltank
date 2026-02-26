<div class="text-center">
    @if($permissions == 'admin' || in_array('fuelTypes_edit', $permissions ?? []))
    <div class="form-check form-switch d-inline-block">
        <input class="form-check-input ft-status-toggle" type="checkbox" role="switch" data-id="{{ $row_object->id }}" {{ $row_object->is_active ? 'checked' : '' }}>
        <label class="form-check-label small">{{ $row_object->is_active ? __('layouts.active') : __('layouts.de-active') }}</label>
    </div>
    @else
    <span class="badge {{ $row_object->is_active ? 'bg-success' : 'bg-warning' }}">{{ $row_object->is_active ? __('layouts.active') : __('layouts.de-active') }}</span>
    @endif
</div>
