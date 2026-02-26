@php
    $count = $row_object->vehicles_count ?? 0;
    $url = route('admin.vehicles.index', ['client_id' => $row_object->id, 'client_name' => $row_object->company_name ?: $row_object->name]);
    $clientName = e($row_object->company_name ?: $row_object->name);
@endphp
<a href="{{ $url }}" class="btn btn-sm btn-outline-primary vehicles-hub-btn" title="{{ $clientName }}">
    <i class="fas fa-car"></i> <span class="badge bg-primary">{{ $count }}</span>
</a>
