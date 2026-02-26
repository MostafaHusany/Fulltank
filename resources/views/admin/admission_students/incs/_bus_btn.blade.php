<div class="text-center">
    @if($row_object->bus != 'no_bus')
    <button class="btn btn-sm {{ isset($row_object->bus_address) ? 'btn-success' : 'btn-warning' }} manage-bus" data-target="{{ $row_object->id }}">
        <i class="fas fa-bus"></i>
    </button>
    @else
    <span class="text-danger">NO BUS</span>
    @endif
</div>