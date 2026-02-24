<div class="d-flex justify-content-center">
    @if(sizeof($row_object->roles))
        @foreach($row_object->roles as $role)
        <span class="badge rounded-pill bg-primary mx-1">{{ $role->display_name }}</span>
        @endforeach
    @else 
        <span>---</span>
    @endif
</div>