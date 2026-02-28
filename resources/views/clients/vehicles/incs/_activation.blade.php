<div class="form-check form-switch d-flex justify-content-center">
    <input class="form-check-input activation-toggle"
        type="checkbox"
        role="switch"
        data-object-id="{{ $row_object->id }}"
        {{ $row_object->status === 'active' ? 'checked' : '' }}>
</div>
