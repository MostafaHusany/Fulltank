@php
    $is_active = $row_object->is_active;
    $status_class = $is_active ? 'success' : 'secondary';
    $status_text = $is_active ? __('station_workers.Active') : __('station_workers.Inactive');
@endphp

@if($permissions == 'admin' || in_array('stationWorkers_edit', $permissions))
<div class="form-check form-switch d-flex justify-content-center">
    <input class="form-check-input worker-status-toggle"
           type="checkbox"
           role="switch"
           data-target="{{ $row_object->id }}"
           {{ $is_active ? 'checked' : '' }}
           style="cursor: pointer;">
</div>
@else
<span class="badge bg-{{ $status_class }}">{{ $status_text }}</span>
@endif
