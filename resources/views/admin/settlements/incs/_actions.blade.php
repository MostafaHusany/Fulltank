<div class="btn-group" role="group">
    @if($permissions == 'admin' || in_array('settlements_show', $permissions))
        <button type="button" class="btn btn-sm btn-outline-secondary show-object" data-target="{{ $row->id }}">
            <i class="fas fa-eye"></i>
        </button>
    @endif
</div>
