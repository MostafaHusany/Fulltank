@extends('layouts.admin.app')

@push('title')
    <h1 class="h2">@lang('layouts.Admission_Students')</h1>
@endpush

@section('content')
    <div id="objectsCard" class="card">
        <div class="card-header">
            <div class="row">
                <div class="col-6 pt-1">
                    @lang('admission_students.Title Administration')
                </div><!-- /.col-6 -->
                <div class="col-6 text-end">
                    
                    @if($permissions == 'admin' || in_array('admissionStudents_edit', $permissions))
                    <button class="bulk-shifting-btn btn btn-sm btn-outline-dark" data-bs-toggle="tooltip" data-bs-placement="top" title="@lang('admission_students.shifting_students')">
                        <i class="fas fa-random"></i>
                    </button>
                    @endif    

                    @if($permissions == 'admin' || in_array('admissionStudents_delete', $permissions))
                    <button class="bulk-delete-btn btn btn-sm btn-outline-dark">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                    @endif

                    <button class="relode-btn btn btn-sm btn-outline-dark">
                        <i class="relode-btn-icon fas fa-sync-alt"></i>
                        <span class="relode-btn-loader spinner-grow spinner-grow-sm" style="display: none;" role="status" aria-hidden="true"></span>
                    </button>

                    <button class="btn btn-sm btn-outline-dark toggle-search">
                        <i class="fas fa-search"></i>
                    </button>

                    @if($permissions == 'admin' || in_array('admissionStudents_add', $permissions))
                    <button class="btn btn-sm btn-outline-primary toggle-btn" data-current-card="#objectsCard" data-target-card="#createObjectCard">
                        <i class="fas fa-plus"></i>
                    </button>
                    @endif
                </div><!-- /.col-6 -->
            </div><!-- /.row -->
        </div><!-- /.card-header -->

        
        <div class="card-body custome-table">
            @include('admin.admission_students.incs._search')

            <div style="overflow-x: scroll">
                <table id="dataTable" class="table text-center">
                    <thead>
                        <tr>
                            <th>@include('layouts.admin.incs._checkbox_select_all')</th>
                            <th>#</th>
                            <th>@lang('admission_students.reference_number')</th>
                            <th>@lang('semesters.Title')</th>
                            <th>@lang('admission_students.Gender')</th>
                            <th>@lang('admissions.Guardian')</th>
                            <th>@lang('admissions.Guardian_Phone')</th>
                            <th>@lang('admission_students.Name')</th>
                            <th>@lang('school_grades.Grade')</th>
                            <th>@lang('school_grades.Level')</th>
                            <th>@lang('school_classes.name')</th>
                            <!-- <th>@lang('admission_students.payment')</th> -->
                            <th>@lang('admission_students.status')</th>
                            <th>@lang('admission_students.semester_payment')</th>
                            <th>@lang('admission_students.bus')</th>
                            <th>@lang('admission_students.account')</th>
                            <th>@lang('admission_students.reports')</th>
                            <th>@lang('admission_students.all_payments')</th>
                            <th>@lang('layouts.Actions')</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div><!-- /.card-body -->
    </div><!-- /.card -->

    @if($permissions == 'admin' || in_array('admissionStudents_add', $permissions))
        @include('admin.admission_students.incs._create')
    @endif
    
    @if($permissions == 'admin' || in_array('admissionStudents_show', $permissions))
        @include('admin.admission_students.incs._show')
    @endif
    
    @if($permissions == 'admin' || in_array('admissionStudents_edit', $permissions))
        @include('admin.admission_students.incs._edit')
        @include('admin.admission_students.incs._bus_form')
        @include('admin.admission_students.incs._auth_form')
        @include('admin.admission_students.incs._shift_form')
    @endif

@endSection

@push('custome-js')
<script>
    $('document').ready(function () {
        window.is_ar = '{{ $is_ar }}';

        // Start MagicTable
        const objects_dynamic_table = new DynamicTable(
            {
                index_route   : "{{ route('admin.admissionStudents.index') }}",
                store_route   : "{{ route('admin.admissionStudents.store') }}",
                show_route    : "{{ route('admin.admissionStudents.index') }}",
                update_route  : "{{ route('admin.admissionStudents.index') }}",
                destroy_route : "{{ route('admin.admissionStudents.index') }}",
                draft           : {
                    route : "{{ route('admin.draft.store') }}",
                    flag  : 'admin.admissions'
                }
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
                draft_obj_btn   : '.create-draft',
                fields_list     : [
                    'id', 'semester_id', 'admission_id', 'first_name', 'second_name', 'third_name',
                    'birth_date', 'grade_id', 'level_id', 'bus', 'gender'
                ],
                imgs_fields     : []
            },
            [
                { data: 'checkbox_selector',        name: 'checkbox_selector', 'orderable': false },
                { data: 'id',                       name: 'id' },
                { data: 'admission_number',         name: 'admission_number' },
                { data: 'semester',                 name: 'semester' },
                { data: 'gender',                   name: 'gender' },
                { data: 'guardian_name',            name: 'guardian_name' },
                { data: 'guardian_phone',           name: 'guardian_phone' },
                { data: 'name',                     name: 'name' },
                { data: 'grade',                    name: 'grade' },
                { data: 'level',                    name: 'level' },
                { data: 'class',                    name: 'class' },
                { data: 'status',                   name: 'status' },
                { data: 'semester_payment',         name: 'semester_payment' },
                { data: 'bus',                      name: 'bus' },
                { data: 'account',                  name: 'account' },
                { data: 'reports',                  name: 'reports' },
                { data: 'all_payments',             name: 'all_payments' },
                { data: 'actions',                  name: 'actions' },
            ],
            function (d) {
                if ($('#s-semesters').length)
                d.semesters = $('#s-semesters').val(); 

                if ($('#s-reference_number').length)
                d.reference_number = $('#s-reference_number').val(); 
                
                if ($('#s-status').length)
                d.status = $('#s-status').val();
            
                if ($('#s-name').length)
                d.name = $('#s-name').val();       
            
                if ($('#s-guardian_name').length)
                d.guardian_name = $('#s-guardian_name').val();       
            
                if ($('#s-grades').length)
                d.grades = $('#s-grades').val();
            
                if ($('#s-levels').length)
                d.levels = $('#s-levels').val();
            
                if ($('#s-classes').length)
                d.classes = $('#s-classes').val();

                if ($('#s-gender').length)
                d.gender = $('#s-gender').val();
            }
        );

        objects_dynamic_table.validateData = (data, prefix = '') => {
            // inite validation flag
            let is_valide = true;

            // clear old validation session
            $('.err-msg').slideUp(500);

            return is_valide;
        };

        objects_dynamic_table.showDataForm = async (targetBtn) => {
        
            let target_id = $(targetBtn).data('object-id');
            let student_keys   = ['name', 'birth_date', 'status'];
            let admission_keys = [
                'family_status',
                'responsible_1_name', 'responsible_1_id_num', 'responsible_1_phone_num', 'responsible_1_email', 'responsible_1_classification',
                'responsible_2_name', 'responsible_2_id_num', 'responsible_2_phone_num', 'responsible_2_email', 'responsible_2_classification',
            ];

            const helpers = {
                grade : (student) => {
                    return Boolean(student.grade)
                    ? (is_ar ? student.grade.ar_title : student.grade.en_title) 
                    : '---';
                },

                level : (student) => {
                    return Boolean(student.level)
                    ? (is_ar ? student.level.ar_title : student.level.en_title) 
                    : '---';
                },

                status : (student) => {
                    return student.status == 'wating' 
                    ? 'text-warning' : (student.status == 'accepted' ? 'text-success' : 'text-danger') ;
                }
            };

            try {
                let response = await axios.get(`{{ route('admin.admissionStudents.index') }}/${target_id}`);

                let { data, success, msg } = response.data;
                
                if (!success)
                throw msg;

                student_keys.forEach(key => {
                    if (Boolean(data[key])) {
                        $(`#show-${key}`).text(data[key]);
                    } else {
                        $(`#show-${key}`).text('---');
                    }
                });

                admission_keys.forEach(key => {
                    if (Boolean(data.admission[key])) {
                        $(`#show-${key}`).text(data.admission[key]);
                    } else {
                        $(`#show-${key}`).text('---');
                    }
                });
                
                $('#show-grade').text(helpers.grade(data));

                $('#show-level').text(helpers.level(data));

                $(`#show-semester`).text(Boolean(data.semester) ? data.semester.title : '---');

                return true;
            } catch (err) {
                window.failerToast(err);
            }

            return false;
        };
        
        objects_dynamic_table.addDataToForm = (fields_id_list, imgs_fields, data, prefix) => {
            $(`#${prefix}level_id, #${prefix}grade_id, #${prefix}admission_id`).empty();
            
            fields_id_list.forEach(el_id => {
                $(`#${prefix + el_id}`).val(Boolean(data[el_id]) ? data[el_id] : '').change();
            });
            
            if (data.admission) {
                let option = new Option(`${data.admission.reference_number} - ${data.admission.responsible_1_name} - ${data.admission.responsible_1_phone_num}`, data.admission.id, true, false);
                $(`#${prefix}admission_id`).append(option).trigger('change');
            }

            if (data.grade) {
                let option = new Option(`${is_ar ? data.grade.ar_title : data.grade.en_title}`, data.grade.id, true, false);
                $(`#${prefix}grade_id`).append(option).trigger('change');
            }

            if (data.level) {
                let option = new Option(`${is_ar ? data.level.ar_title : data.level.en_title}`, data.level.id, true, false);
                $(`#${prefix}level_id`).append(option).trigger('change');
            }

            if (data.semester) {
                let option = new Option(`${data.semester.title}`, data.semester.id, true, false);
                $(`#${prefix}semester_id`).append(option).trigger('change');
            }

            $('#edit-id').val(data.id);
        };

        const init = (() => {
            
            $('#dreafted_data').select2({
                allowClear: true,
                width: '100%',
                placeholder: `@lang('layouts.Select_Draft')`,
                ajax: {
                    url: '{{ route("admin.search.drafts") }}',
                    dataType: 'json',
                    delay: 150,
                    data : function (params) {
                        var query = {
                            q  : params.term,
                            section_flag : 'admin.admissions'
                        }
                        return query;
                    },
                    processResults : function(data) {
                        return {
                            results: $.map(data, function(item) {
                                return {
                                    text    : item.title,
                                    id      : item.id
                                }
                            })
                        };
                    },
                    cache: true
                }
            });

            $('#s-semesters, #semester_id, #edit-semester_id, #shifting-semester_id').select2({
                allowClear: true,
                width: '100%',
                placeholder: '@lang("layouts.Select_Semester")',
                ajax: {
                    url: `{{ route('admin.search.semesters') }}`,
                    dataType: 'json',
                    delay: 150,
                    processResults: function (data) {
                        return {
                            results:  $.map(data, function (item) {
                                return {
                                    text: item.title,
                                    id: item.id
                                }
                            })
                        };
                    },
                    cache: true
                }
            });

            $('#admission_id, #edit-admission_id').select2({
                allowClear: true,
                width: '100%',
                placeholder: `@lang('layouts.Select_Admission')`,
                ajax: {
                    url: '{{ route("admin.search.admissions") }}',
                    dataType: 'json',
                    delay: 150,
                    processResults : function(data) {
                        return {
                            results: $.map(data, function(item) {
                                return {
                                    text    : `${item.reference_number} - ${item.responsible_1_name} - ${item.responsible_1_phone_num}`,
                                    id      : item.id
                                }
                            })
                        };
                    },
                    cache: true
                }
            });

            $('#s-classes').select2({
                allowClear: true,
                width: '100%',
                placeholder: `@lang('layouts.Select_Classess')`,
                ajax: {
                    url: '{{ route("admin.search.classes") }}',
                    dataType: 'json',
                    delay: 150,
                    processResults : function(data) {
                        return {
                            results: $.map(data, function(item) {
                                return {
                                    text    : item.name,
                                    id      : item.id
                                }
                            })
                        };
                    },
                    cache: true
                }
            });

            $('#s-grades, #grade_id, #edit-grade_id, #shifting-grade_id').select2({
                allowClear: true,
                // dropdownParent: $('#students-editForm .modal-content'),
                width: '100%',
                placeholder: '@lang("layouts.Select_Grade")',
                ajax: {
                    url: `{{ route('admin.search.grades') }}`,
                    dataType: 'json',
                    delay: 150,
                    data: function (params) {
                        var query = {
                            q  : params.term,
                            is_main  : true
                        }
                        return query;
                    },
                    processResults: function (data) {
                        return {
                            results:  $.map(data, function (item) {
                                return {
                                    text: is_ar ? item.ar_title : item.en_title,
                                    id: item.id
                                }
                            })
                        };
                    },
                    cache: true
                }
            }).change(function () {
                window.grade_ids = $(this).val(); 
                let target = $(this).data('target');
                
                window.grade_ids != null && window.grade_ids.length > 0
                    ? $(target).val('').removeAttr('disabled').trigger('change')
                    : $(target).val('').attr('disabled', 'disabled').trigger('change');
                
            });

            $('#s-levels, #level_id, #edit-level_id, #shifting-level_id').select2({
                allowClear: true,
                // dropdownParent: $('#students-editForm .modal-content'),
                width: '100%',
                placeholder: '@lang("layouts.Select_Level")',
                ajax: {
                    url: `{{ route('admin.search.grades') }}`,
                    dataType: 'json',
                    delay: 150,
                    data: function (params) {
                        var query = {
                            q        : params.term,
                            is_sub   : true, 
                            grade_ids : window.grade_ids
                        }
                        return query;
                    },
                    processResults: function (data) {
                        return {
                            results:  $.map(data, function (item) {
                                return {
                                    text: is_ar ? item.ar_title : item.en_title,
                                    id: item.id
                                }
                            })
                        };
                    },
                    cache: true
                }
            });

            $('#dataTable').on('click', '.students-status', async function () {
                let status    = $(this).data('status');
                let target_id = $(this).data('target');

                if (!Boolean(target_id)) return -1;

                $(`#parent-btn-${target_id}`).attr('disabled', 'disabled');
                $(window.loddingSpinnerEl).fadeIn(500);

                try {

                    let res = await axios.post(`{{ route('admin.admissionStudents.index') }}/${target_id}`, {
                        status,
                        _token        : "{{ csrf_token() }}",
                        _method       : 'PUT',
                        update_status : true,
                    });

                    let { data, success, msg } = res.data;

                    if (!success) throw msg;

                    $('.relode-btn').trigger('click');

                    successToast(`@lang('admission_students.object_updated')`);

                } catch (err) {
                    failerToast(typeof(err) == 'string' ? err : `@lang('admission_students.object_error')`);
                }
                
                $(`#parent-btn-${target_id}`).removeAttr('disabled');
                $(window.loddingSpinnerEl).fadeOut(500);
                    
            });

            (() => {
                const searchParams = new URLSearchParams(window.location.search);
                
                if (searchParams.has('reference_number')) {
                    $('.search-container').slideDown(500);
                    $('#s-reference_number').val(searchParams.get('reference_number')).trigger('change');
                }
            })();

        })();
        
    });
</script>
@endpush