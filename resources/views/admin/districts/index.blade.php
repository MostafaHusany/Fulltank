@extends('layouts.admin.app')

@push('custome-plugin')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
@endpush

@push('title')
    <h1 class="h2">@lang('layouts.Districts')</h1>
@endpush

@section('content')
    <div id="objectsCard" class="card">
        <div class="card-header">
            <div class="row">
                <div class="col-6 pt-1">
                    @lang('districts.Title Administration')
                </div><!-- /.col-6 -->
                <div class="col-6 text-end">
                
                    @if($permissions == 'admin' || in_array('districts_delete', $permissions))
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

                    @if($permissions == 'admin' || in_array('districts_add', $permissions))
                    <button class="btn btn-sm btn-outline-primary toggle-btn" data-current-card="#objectsCard" data-target-card="#createObjectCard">
                        <i class="fas fa-plus"></i>
                    </button>
                    @endif
                </div><!-- /.col-6 -->
            </div><!-- /.row -->
        </div><!-- /.card-header -->

        
        <div class="card-body custome-table">
            @include('admin.districts.incs._search')

            <table id="dataTable" class="table text-center">
                <thead>
                    <tr>
                        <th>@include('layouts.admin.incs._checkbox_select_all')</th>
                        <th>#</th>
                        <th>@lang('districts.Governorate')</th>
                        <th>@lang('districts.Centers')</th>
                        <th>@lang('layouts.Active')</th>
                        <th>@lang('layouts.Actions')</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div><!-- /.card-body -->
    </div><!-- /.card -->

    
    @if($permissions == 'admin' || in_array('districts_add', $permissions))
        @include('admin.districts.incs._create')
    @endif
        
    @if($permissions == 'admin' || in_array('districts_show', $permissions))
        @include('admin.districts.incs._show')
    @endif 
    
    @if($permissions == 'admin' || in_array('districts_edit', $permissions))
        @include('admin.districts.incs._edit')
        @include('admin.districts.incs._centers_form')
    @endif

@endSection

@push('custome-js')
<script>
    $('document').ready(function () {
            
        const objects_dynamic_table = new DynamicTable(
            {
                index_route   : "{{ route('admin.districts.index') }}",
                store_route   : "{{ route('admin.districts.store') }}",
                show_route    : "{{ route('admin.districts.index') }}",
                update_route  : "{{ route('admin.districts.index') }}",
                destroy_route : "{{ route('admin.districts.index') }}",
                draft         : {
                    route : "{{ route('admin.draft.store') }}",
                    flag  : 'admin.districts'
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
                    'id', 'ar_name', 'en_name',
                    'geo_lat', 'geo_lng'
                    // 'centers', 
                ],
                imgs_fields     : []
            },
            [
                { data: 'checkbox_selector',    name: 'checkbox_selector', 'orderable': false },
                { data: 'id',                   name: 'id' },
                { data: 'name',                 name: 'name' },
                { data: 'centers_btn',          name: 'centers_btn' },
                { data: 'activation',           name: 'activation' },
                { data: 'actions',              name: 'actions' },
            ],
            function (d) {

                if ($('#s-name').length)
                d.name = $('#s-name').val(); 
            
                if ($('#s-is_active').length)
                d.is_active = $('#s-is_active').val(); 

            }
        );

        objects_dynamic_table.validateData = (data, prefix = '') => {
            // inite validation flag
            let is_valide = true;

            // clear old validation session
            $('.err-msg').slideUp(500);

            if (data.get('ar_name') === '') {
                is_valide = false;
                let err_msg = '@lang("districts.ar_name_required")';
                $(`#${prefix}ar_nameErr`).text(err_msg);
                $(`#${prefix}ar_nameErr`).slideDown(500);
            }

            if (data.get('en_name') === '') {
                is_valide = false;
                let err_msg = '@lang("districts.en_name_required")';
                $(`#${prefix}en_nameErr`).text(err_msg);
                $(`#${prefix}en_nameErr`).slideDown(500);
            }

            if (data.get('geo_lat') === '' || data.get('geo_lng') === '') {
                is_valide = false;
                let err_msg = '@lang("districts.geo_requiredied")';
                $(`#${prefix}geo_latErr`).text(err_msg);
                $(`#${prefix}geo_latErr`).slideDown(500);
            }

            return is_valide;
        };
    
        objects_dynamic_table.showDataForm = async (targetBtn) => {
        
            let target_user = $(targetBtn).data('object-id');
            let keys = ['name', 'email', 'phone', 'category'];
            
            let response = await axios.get(`{{ url('admin/districts') }}/${target_user}`);

            let { data, success, msg } = response.data;
            
            if (success) {
                    
                $('#show-ar_name').val(data.ar_name);
                $('#show-en_name').val(data.en_name);
                
                let district_tr = ``;
                data.children.forEach(sub_district => {
                    district_tr += `
                        <tr>
                            <td><span class="badge rounded-pill bg-primary mx-1">${sub_district.ar_name}</span> <span class="badge rounded-pill bg-primary mx-1">${sub_district.en_name}</span></td>
                        </tr>
                    `;
                });

                $('#show-childrenTableContainer').html(district_tr);

                return true;
            } else {
                Toastify({
                    text: msg,
                    className: "info",
                    offset: {
                        x: 20, // horizontal axis - can be a number or a string indicating unity. eg: '2em'
                        y: 50 // vertical axis - can be a number or a string indicating unity. eg: '2em'
                    },
                    style: {
                        color: '#842029', background: '#f8d7da', borderColor: '#f5c2c7'
                    }
                }).showToast();
            }

            return false;
        };
            
        objects_dynamic_table.addDataToForm = (fields_id_list, imgs_fields, data, prefix) => {
            fields_id_list.forEach(el_id => {
                $(`#${prefix + el_id}`).val(data[el_id]).change();
            });

            window.edit_map = Boolean(window.edit_map) ? window.edit_map.remove() : null;

            setTimeout(() => {
                window.edit_map = L.map(`${prefix}map`, {zoomControl : true, zoom: 3, scrollWheelZoom: false, attributionControl: false}).setView([30.012086, 31.215356], 7);
                
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
                }).addTo(window.edit_map);

                window.editMarkerGroup = L.layerGroup().addTo(window.edit_map);
                
                axios.post(`https://maps.googleapis.com/maps/api/geocode/json?address=${data.name_ar}&key={{ ENV('GOOGLE_MAPS_KEYS') }}`)
                .then(res => {
                    if (Boolean(res.data.results) && res.data.results.length) {
                        let { lat, lng } = res.data.results[0].geometry.location
                    
                        editMarkerGroup.clearLayers();
                        L.marker([lat, lng], {draggable :true}).addTo(editMarkerGroup);
                        edit_map.setView([lat, lng], 10);
                        
                        $(`#${prefix}geo_lat`).val(lat)
                        $(`#${prefix}geo_lng`).val(lng)
                    }
                });

                window.edit_map.on('click', function (e) {
                    let {lat, lng} = e.latlng;
                    
                    $(`#${prefix}geo_lat`).val(lat)
                    $(`#${prefix}geo_lng`).val(lng)
                    
                    editMarkerGroup.clearLayers();
                    L.marker([lat, lng], {draggable :true}).addTo(editMarkerGroup);
                });
                
                
                L.marker([data.geo_lat, data.geo_lng], {draggable :true}).addTo(editMarkerGroup);

            }, 1000);
        }

        const init = (() => {
          

        })();
        
    });
</script>
@endpush