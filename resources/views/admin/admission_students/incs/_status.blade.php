<?php 

    $span_text    = '';
    $button_color = '';

    // 'research', 'phase_2', 'phase_3', 'closed'

    if ($row_object->status == 'wating') {
        $span_text    = __('admissions.wating');
        $button_color = 'warning';
    } elseif($row_object->status == 'accepted') {
        $span_text    = __('admissions.accepted');
        $button_color = 'success';
    } elseif ($row_object->status == 'rejected') {
        $span_text    = __('admissions.rejected');
        $button_color = 'danger';
    }
?>

@if($permissions == 'admin' || in_array('admissionStudents_edit', $permissions))
<div class="text-center">
    <div class="btn-group">
        <button id="parent-btn-{{$row_object->id}}" class="btn btn-{{ $button_color }} btn-sm dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="fas fa-flag"></i>

            <span class="px-2">{{ $span_text }}</span>
        </button>
        <div class="dropdown-menu {{ !$is_ar ? 'dropdown-menu-right dropdown-menu-lg-right' : 'dropdown-menu-left dropdown-menu-lg-left' }}">

            <button class="dropdown-item students-status text-warning" 
                data-target="{{$row_object->id}}" data-action-type="status" data-status="wating"
            >
                <div class="row">
                    <div class="col-8 text-left">
                        <span>@lang('admissions.wating')</span>
                    </div>
                    <div class="col-4">
                        <i class="fas fa-flag"></i>
                    </div>
                </div><!-- /.row -->
            </button>
            
            <button class="dropdown-item students-status text-success" 
                data-target="{{$row_object->id}}" data-action-type="status" data-status="accepted"
            >
                <div class="row">
                    <div class="col-8 text-left">
                        <span>@lang('admissions.accepted')</span>
                    </div>
                    <div class="col-4">
                        <i class="fas fa-flag"></i>
                    </div>
                </div><!-- /.row -->
            </button>
            
            <div class="dropdown-divider"></div>

            <button class="dropdown-item students-status text-danger" 
                data-target="{{$row_object->id}}" data-action-type="status" data-status="rejected"
            >
                <div class="row">
                    <div class="col-8 text-left">
                        <span>@lang('admissions.rejected')</span>
                    </div>
                    <div class="col-4">
                        <i class="fas fa-flag"></i>
                    </div>
                </div><!-- /.row -->
            </button>
            
        </div>
    </div>
</div>
@else
    <span class="badge bg-{{$button_color}}">{{ $span_text }}</span>
@endif