@php
    $count = $row_object->drivers_count ?? 0;
    $url = route('admin.drivers.index', ['client_id' => $row_object->id, 'client_name' => $row_object->company_name ?: $row_object->name]);
    $clientName = e($row_object->company_name ?: $row_object->name);
@endphp
<a href="{{ $url }}" class="btn btn-sm btn-outline-info drivers-hub-btn" title="{{ $clientName }}">
    <i class="fas fa-id-badge"></i> <span class="badge bg-info text-dark">{{ $count }}</span>
</a>
