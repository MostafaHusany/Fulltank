<div class="d-flex justify-content-center">
	@if($permissions == 'admin' || in_array('vehicles_edit', $permissions))
	<div class="form-check form-switch">
		<input class="c-activation-btn form-check-input" id="customSwitch{{ $row_object->id }}" data-target-obj="{{ $row_object->id }}" type="checkbox" @if($row_object->status === 'active') checked="true" @endif>
		<label class="form-check-label" for="customSwitch{{ $row_object->id }}"></label>
	</div>
	@else
	{!!
		$row_object->status === 'active' ? '<span class="badge bg-primary">active</span>' : '<span class="badge bg-warning">de-active</span>'
	!!}
	@endif
</div>

