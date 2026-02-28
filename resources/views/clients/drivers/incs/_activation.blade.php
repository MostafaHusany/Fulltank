<div class="text-center">
    <div class="form-check form-switch d-inline-block">
        <input class="form-check-input activation-toggle" 
            type="checkbox" 
            role="switch" 
            data-object-id="{{ $row_object->id }}"
            {{ $row_object->is_active ? 'checked' : '' }}>
    </div>
</div>
