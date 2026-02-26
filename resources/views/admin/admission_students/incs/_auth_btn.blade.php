<div class="text-center">
    @if ($row_object->user_id)
    <button class="btn btn-sm btn-warning edit-student-account" data-target="{{ $row_object->id }}">
        <i class="fas fa-user-cog"></i>
    </button>
    @else 
    <span class="text-danger">NO ACCOUNT</span>
    @endif
</div>