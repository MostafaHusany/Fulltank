@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Dashboard') }}</div>

                <div class="card-body">
                    <table id="dataTable" class="text-center table table-sm table-bordered text-center">
                        <thead class="text-center ">
                            <th>#</th>
                            <th>@lang('Name')</th>
                            <th>@lang('Email')</th>
                            <th>@lang('Phone')</th>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection


@push('custome-js')
<script>
    $('document').ready(function () {
            
        const objects_dynamic_table = new DynamicTable(
            {
                index_route   : "{{ route('home') }}",
                store_route   : "{{ route('home') }}",
                show_route    : "{{ url('admin/users') }}",
                update_route  : "{{ url('admin/users') }}",
                destroy_route : "{{ url('admin/users') }}",
            },
            '#dataTable',
            {
                success_el : '#successAlert',
                danger_el  : '#dangerAlert',
                warning_el : '#warningAlert'
            },
            {
                table_id        : '#dataTable',
                toggle_btn      : '.toggle-btn',
                create_obj_btn  : '.create-object',
                update_obj_btn  : '.update-object',
                fields_list     : [
                    'id', 'name', 'email', 'phone',
                    'category', 'password', 'group_id',
                    'role', 'permissions', 'is_custome_permissions'
                ],
                imgs_fields     : []
            },
            [
                { data: 'id', name: 'id' },
                { data: 'name', name: 'name' },
                { data: 'email', name: 'email' },
                { data: 'phone', name: 'phone' }
            ],
            function (d) {
                if ($('#s-name').length)
                d.name = $('#s-name').val(); 

                if ($('#s-email').length)
                d.email = $('#s-email').val();  
                
                if ($('#s-phone').length)
                d.phone = $('#s-phone').val();  
                
                if ($('#s-category').length)
                d.category = $('#s-category').val();       
                
                if ($('#s-is_active').length)
                d.is_active = $('#s-is_active').val();    

                if ($('#s-roles').length)
                d.roles = $('#s-roles').val();
            
                if ($('#s-group_id').length)
                d.group_id = $('#s-group_id').val();
            }
        );
        
    });
</script>
@endpush