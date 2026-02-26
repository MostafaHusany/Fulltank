@php
    $is_ar = LaravelLocalization::getCurrentLocale() == 'ar';
@endphp
<div class="text-center">
    @if($permissions == 'admin' || sizeof($permissions) > 0)
    <div class="btn-group">
        <button type="button" class="btn btn-outline-dark btn-sm dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="fas fa-sliders-h"></i>
        </button>
        <div class="dropdown-menu {{ !$is_ar ? 'dropdown-menu-end' : '' }}">

            @if($permissions == 'admin' || in_array('drivers_show', $permissions))
            <button class="dropdown-item show-object text-info" data-object-id="{{ $row_object->id }}"
                data-current-card="#objectsCard"
                data-target-card="#showObjectsCard">
                <div class="row">
                    <div class="col-8 text-start"><span>@lang('layouts.show')</span></div>
                    <div class="col-4"><i class="fas fa-eye float-end"></i></div>
                </div>
            </button>
            @endif

            @if($permissions == 'admin' || in_array('drivers_edit', $permissions))
            <button class="dropdown-item edit-object text-warning"
                data-object-id="{{ $row_object->id }}"
                data-current-card="#objectsCard"
                data-target-card="#editObjectsCard">
                <div class="row">
                    <div class="col-8 text-start"><span>@lang('layouts.edit')</span></div>
                    <div class="col-4"><i class="fas fa-edit float-end"></i></div>
                </div>
            </button>
            @endif

            @if($permissions == 'admin' || in_array('drivers_delete', $permissions))
            <div class="dropdown-divider"></div>
            <button class="dropdown-item delete-object text-danger"
                data-object-id="{{ $row_object->id }}"
                data-object-name="{{ $row_object->name }}">
                <div class="row">
                    <div class="col-8 text-start"><span>@lang('layouts.delete')</span></div>
                    <div class="col-4"><i class="fas fa-trash-alt float-end"></i></div>
                </div>
            </button>
            @endif
        </div>
    </div>
    @else
    <span>---</span>
    @endif
</div>
