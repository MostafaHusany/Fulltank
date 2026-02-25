@php
    $count = $row_object->client_documents_count ?? 0;
@endphp
<div class="text-center">
    @if($permissions == 'admin' || in_array('clients_show', $permissions) || in_array('clients_edit', $permissions))
    <button type="button" class="btn btn-sm btn-outline-primary client-documents-btn" data-client-id="{{ $row_object->id }}" data-client-name="{{ $row_object->name }}">
        <i class="fas fa-file-alt"></i> {{ $count }}
    </button>
    @else
    <span class="badge bg-secondary">{{ $count }}</span>
    @endif
</div>
