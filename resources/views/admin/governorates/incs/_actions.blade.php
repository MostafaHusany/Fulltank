@php $is_ar = LaravelLocalization::getCurrentLocale() == 'ar'; @endphp
<div class="text-center">
    @if($permissions == 'admin' || sizeof($permissions ?? []) > 0)
    <div class="btn-group">
        <button type="button" class="btn btn-outline-dark btn-sm dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false"><i class="fas fa-sliders-h"></i></button>
        <div class="dropdown-menu {{ !$is_ar ? 'dropdown-menu-end' : 'dropdown-menu-start' }}">
            @if($permissions == 'admin' || in_array('governorates_show', $permissions ?? []))
                <button class="dropdown-item show-object text-info" data-object-id="{{ $row_object->id }}" data-current-card="#objectsCard" data-target-card="#showObjectsCard"><span>@lang('layouts.show')</span><i class="fas fa-eye float-end"></i></button>
            @endif

            @if($permissions == 'admin' || in_array('governorates_edit', $permissions ?? []))
                <button class="dropdown-item edit-object text-warning" data-object-id="{{ $row_object->id }}" data-current-card="#objectsCard" data-target-card="#editObjectsCard"><span>@lang('layouts.edit')</span><i class="fas fa-edit float-end"></i></button>
            @endif
            
            @if($permissions == 'admin' || in_array('governorates_delete', $permissions ?? []))
                <div class="dropdown-divider"></div>
                <button class="dropdown-item delete-object text-danger" data-object-id="{{ $row_object->id }}" data-object-name="{{ $row_object->name }}"><span>@lang('layouts.delete')</span><i class="fas fa-trash-alt float-end"></i></button>
            @endif
        </div>
    </div>
    @else
    <span>---</span>
    @endif
</div>
