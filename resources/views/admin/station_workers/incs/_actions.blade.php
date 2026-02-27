@php
    $is_ar = LaravelLocalization::getCurrentLocale() == 'ar';
@endphp

<div class="text-center">
    @if($permissions == 'admin' || sizeof($permissions) > 0)
    <div class="btn-group">
        <button type="button" class="btn btn-outline-dark btn-sm dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="fas fa-sliders-h"></i>
        </button>
        <div class="dropdown-menu {{ !$is_ar ? '!dropdown-menu-right !dropdown-menu-lg-right' : '!dropdown-menu-left !dropdown-menu-lg-left' }}">

            @if($permissions == 'admin' || in_array('stationWorkers_edit', $permissions))
            <button class="dropdown-item edit-object text-warning"
                data-object-id="{{ $row_object->id }}"
                data-current-card="#objectsCard"
                data-target-card="#editObjectsCard"
            >
                <div class="row">
                    <div class="col-8 text-left">
                        <span>@lang('layouts.edit')</span>
                    </div>
                    <div class="col-4">
                        <i class="fas fa-edit float-end"></i>
                    </div>
                </div>
            </button>
            @endif

            @if($permissions == 'admin' || in_array('stationWorkers_delete', $permissions))
            <div class="dropdown-divider"></div>

            <button class="dropdown-item delete-object text-danger"
                data-object-id="{{ $row_object->id }}" data-object-name="{{ $row_object->full_name }}"
            >
                <div class="row">
                    <div class="col-8 text-left">
                        <span>@lang('layouts.delete')</span>
                    </div>
                    <div class="col-4">
                        <i class="fas fa-trash-alt float-end"></i>
                    </div>
                </div>
            </button>
            @endif

        </div>
    </div>
    @else
    <span>---</span>
    @endif
</div>
