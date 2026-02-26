@extends('layouts.admin.app')

@push('title')
    <h1 class="h2">@lang('stations.Title')</h1>
@endpush

@section('content')
    <div id="objectsCard" class="card">
        <div class="card-header">
            <div class="row">
                <div class="col-6 pt-1">
                    @lang('stations.Title')
                </div><!-- /.col-6 -->
                <div class="col-6 text-end">
                    <button class="relode-btn btn btn-sm btn-outline-dark">
                        <i class="relode-btn-icon fas fa-sync-alt"></i>
                        <span class="relode-btn-loader spinner-grow spinner-grow-sm" style="display: none;" role="status" aria-hidden="true"></span>
                    </button>
                    <button class="btn btn-sm btn-outline-dark toggle-search">
                        <i class="fas fa-search"></i>
                    </button>
                    @if($permissions == 'admin' || in_array('stations_add', $permissions ?? []))
                    <button class="btn btn-sm btn-outline-primary toggle-btn" data-current-card="#objectsCard" data-target-card="#createObjectCard">
                        <i class="fas fa-plus"></i>
                    </button>
                    @endif
                </div><!-- /.col-6 -->
            </div><!-- /.row -->
        </div><!-- /.card-header -->
        <div class="card-body custome-table">
            @include('admin.stations.incs._search')
            <div style="overflow-x: scroll">
                <table id="dataTable" class="table text-center">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>@lang('stations.Name')</th>
                            <th>@lang('stations.Governorate')</th>
                            <th>@lang('stations.District')</th>
                            <th>@lang('stations.Manager Name')</th>
                            <th>@lang('stations.Phone 1')</th>
                            <th>@lang('stations.Email')</th>
                            <th>@lang('stations.Account Status')</th>
                            <th>@lang('layouts.Actions')</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div><!-- /.card-body -->
    </div><!-- /.card -->

    @if($permissions == 'admin' || in_array('stations_add', $permissions ?? []))
        @include('admin.stations.incs._create')
    @endif
    @if($permissions == 'admin' || in_array('stations_show', $permissions ?? []))
        @include('admin.stations.incs._show')
    @endif
    @if($permissions == 'admin' || in_array('stations_edit', $permissions ?? []))
        @include('admin.stations.incs._edit')
    @endif
@endSection

@push('custome-plugin')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin="" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
@endpush

@push('custome-js')
<script>
    $('document').ready(function () {
        const ROUTES = {
            index   : "{{ route('admin.stations.index') }}",
            store   : "{{ route('admin.stations.store') }}",
            show    : "{{ route('admin.stations.show', ['id' => 'ID']) }}",
            update  : "{{ route('admin.stations.update', ['id' => 'ID']) }}",
            destroy : "{{ route('admin.stations.destroy', ['id' => 'ID']) }}",
            toggleAccount : "{{ route('admin.stations.toggleAccount', ['id' => 'ID']) }}",
            governors : "{{ route('admin.search.governorates') }}",
            districts : "{{ route('admin.search.districts') }}",
            fuelTypesList : "{{ route('admin.fuelTypes.list') }}"
        };

        const objects_dynamic_table = new DynamicTable(
            {
                index_route   : ROUTES.index,
                store_route   : ROUTES.index,
                show_route    : ROUTES.index,
                update_route  : ROUTES.index,
                destroy_route : ROUTES.index,
                draft         : { route: '', flag: '' }
            },
            '#dataTable',
            { success_el: '#successAlert', danger_el: '#dangerAlert', warning_el: '#warningAlert' },
            {
                table_id       : '#dataTable',
                toggle_btn     : '.toggle-btn',
                create_obj_btn : '.create-object',
                update_obj_btn : '.update-object',
                draft_obj_btn  : '',
                fields_list    : ['id', 'name', 'governorate_id', 'district_id', 'address', 'lat', 'lng', 'nearby_landmarks', 'manager_name', 'phone_1', 'phone_2', 'bank_account_details', 'email', 'password', 'fuel_type_ids'],
                imgs_fields    : []
            },
            [
                { data: 'id', name: 'id' },
                { data: 'name', name: 'name' },
                { data: 'governorate_name', name: 'governorate_name' },
                { data: 'district_name', name: 'district_name' },
                { data: 'manager_name', name: 'manager_name' },
                { data: 'phone_1', name: 'phone_1' },
                { data: 'email', name: 'email' },
                { data: 'account_status', name: 'account_status' },
                { data: 'actions', name: 'actions' }
            ],
            function (d) {
                if ($('#s-name').length) d.name = $('#s-name').val();
                if ($('#s-governorate_id').length) d.governorate_id = $('#s-governorate_id').val();
                if ($('#s-district_id').length) d.district_id = $('#s-district_id').val();
                if ($('#s-manager_name').length) d.manager_name = $('#s-manager_name').val();
                if ($('#s-phone_1').length) d.phone_1 = $('#s-phone_1').val();
                if ($('#s-email').length) d.email = $('#s-email').val();
            }
        );

        objects_dynamic_table.table_object.buttons().container().hide();

        var _originalGetFromData = objects_dynamic_table._getFromData;
        objects_dynamic_table._getFromData = function (fields_list, imgs_fields, prefix) {
            var data = _originalGetFromData.call(this, fields_list, imgs_fields, prefix);
            var p = prefix || '';
            var ids = $('#' + p + 'fuel_type_ids').val();
            if (ids != null && ids !== '') {
                data.delete('fuel_type_ids');
                var arr = Array.isArray(ids) ? ids : (typeof ids === 'string' ? ids.split(',').map(function (s) { return s.trim(); }).filter(Boolean) : [ids]);
                arr.forEach(function (id) { data.append('fuel_type_ids[]', id); });
            }
            return data;
        };

        objects_dynamic_table.addDataToForm = function (fields_list, imgs_fields, data, prefix) {
            var p = prefix || '';

            if (p === 'edit-') {
                // Simple fields
                $('#edit-id').val(data.id);
                $('#edit-name').val(data.name || '');
                $('#edit-address').val(data.address || '');
                $('#edit-lat').val(data.lat != null ? data.lat : '');
                $('#edit-lng').val(data.lng != null ? data.lng : '');
                $('#edit-nearby_landmarks').val(data.nearby_landmarks || '');
                $('#edit-manager_name').val(data.manager_name || '');
                $('#edit-phone_1').val(data.phone_1 || '');
                $('#edit-phone_2').val(data.phone_2 || '');
                $('#edit-bank_account_details').val(data.bank_account_details || '');

                // Governorate (Select2)
                if (data.governorate) {
                    var govOpt = new Option(data.governorate.name, data.governorate.id, true, true);
                    $('#edit-governorate_id').empty().append(govOpt).trigger('change');
                }

                // District (Select2)
                if (data.district) {
                    var distOpt = new Option(data.district.name, data.district.id, true, true);
                    $('#edit-district_id').empty().append(distOpt).trigger('change');
                }

                // Fuel types (Select2 multi)
                if (data.fuel_types && data.fuel_types.length) {
                    var fuelIds = data.fuel_types.map(function (ft) { return ft.id; });
                    $('#edit-fuel_type_ids').val(fuelIds).trigger('change');
                }

                // Map marker
                if (data.lat != null && data.lng != null && window.stationMapEdit) {
                    window.stationMapEdit.setMarker(parseFloat(data.lat), parseFloat(data.lng));
                }
                return;
            }

            // Default: iterate over fields_list
            fields_list.forEach(function (f) {
                if (f === 'id') return;

                if (f === 'fuel_type_ids' && data.fuel_types) {
                    var ids = data.fuel_types.map(function (ft) { return ft.id; });
                    if ($('#fuel_type_ids').length) $('#fuel_type_ids').val(ids).trigger('change');
                    return;
                }

                var el = $('#' + p + f);
                if (el.length) {
                    el.val(data[f] != null ? data[f] : '').change();
                }
            });
        };

        objects_dynamic_table.showDataForm = async function (targetBtn) {
            var id = $(targetBtn).data('object-id');
            try {
                var res = await axios.get(ROUTES.show.replace('ID', id));
                var d = res.data;
                if (d.success && d.data) {
                    $('#show-name').text(d.data.name || '---');
                    $('#show-governorate').text(d.data.governorate ? d.data.governorate.name : '---');
                    $('#show-district').text(d.data.district ? d.data.district.name : '---');
                    $('#show-address').text(d.data.address || '---');
                    $('#show-manager_name').text(d.data.manager_name || '---');
                    $('#show-phone_1').text(d.data.phone_1 || '---');
                    $('#show-phone_2').text(d.data.phone_2 || '---');
                    var ftNames = (d.data.fuel_types || []).map(function (ft) { return ft.name; }).join(', ');
                    $('#show-fuel_types').text(ftNames || '---');
                    return true;
                }
                failerToast(d.msg || 'Error');
            } catch (e) {
                failerToast(e.response && e.response.data && e.response.data.msg ? e.response.data.msg : 'Error');
            }
            return false;
        };

        $(document).on('change', '.station-account-toggle', function () {
            var id = $(this).data('id');
            var sw = $(this);
            sw.prop('disabled', true);
            axios.put(ROUTES.toggleAccount.replace('ID', id), { _token: $('meta[name="csrf-token"]').attr('content'), _method: 'PUT' }).then(function (r) {
                if (r.data.success) { successToast(r.data.msg); objects_dynamic_table.table_object.draw(); }
                else { sw.prop('checked', !sw.prop('checked')); failerToast(r.data.msg || 'Error'); }
            }).catch(function (e) {
                sw.prop('checked', !sw.prop('checked'));
                failerToast(e.response && e.response.data && e.response.data.msg ? e.response.data.msg : 'Error');
            }).finally(function () { sw.prop('disabled', false); });
        });

        $('.relode-btn').on('click', function () { objects_dynamic_table.table_object.draw(); });

        (function initSelect2AndMap() {
            // Governorate Select2
            var governorateAjax = {
                url: ROUTES.governors,
                dataType: 'json',
                delay: 150,
                data: function (p) { return { q: p.term }; },
                processResults: function (d) {
                    return { results: (d || []).map(function (g) { return { id: g.id, text: g.text }; }) };
                },
                cache: true
            };
            $('#governorate_id, #edit-governorate_id, #s-governorate_id').select2({
                allowClear: true,
                width: '100%',
                placeholder: '@lang("stations.Governorate")',
                ajax: governorateAjax
            });

            // District Select2
            function initDistrict(govId, distId) {
                var districtAjax = {
                    url: ROUTES.districts,
                    dataType: 'json',
                    delay: 150,
                    data: function (p) { return { q: p.term, governorate_id: $(govId).val() }; },
                    processResults: function (d) {
                        return { results: (d || []).map(function (x) { return { id: x.id, text: x.text }; }) };
                    },
                    cache: true
                };
                $(distId).select2({
                    allowClear: true,
                    width: '100%',
                    placeholder: '@lang("stations.District")',
                    ajax: districtAjax
                });
            }
            initDistrict('#governorate_id', '#district_id');
            initDistrict('#edit-governorate_id', '#edit-district_id');
            initDistrict('#s-governorate_id', '#s-district_id');

            // Clear district when governorate changes
            $('#governorate_id').on('change', function () { $('#district_id').val(null).trigger('change'); });
            $('#edit-governorate_id').on('change', function () { $('#edit-district_id').val(null).trigger('change'); });
            $('#s-governorate_id').on('change', function () { $('#s-district_id').val(null).trigger('change'); });

            // Fuel Types Select2
            axios.get(ROUTES.fuelTypesList).then(function (r) {
                var opts = (r.data.data || []).map(function (f) { return { id: f.id, text: f.name }; });
                $('#fuel_type_ids, #edit-fuel_type_ids').select2({
                    allowClear: true,
                    width: '100%',
                    placeholder: '@lang("stations.Fuel Types")',
                    data: opts
                });
            });

            // Maps (Leaflet)
            var EGYPT_CENTER = [30.0444, 31.2357];
            if (typeof L !== 'undefined') {
                function initMapCreate() {
                    if (!$('#stationMapCreate').length) return;
                    var mapCreate = L.map('stationMapCreate', { center: EGYPT_CENTER, zoom: 6 }).setView(EGYPT_CENTER, 6);
                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '&copy; OpenStreetMap' }).addTo(mapCreate);
                    window.stationMapCreate = {
                        map: mapCreate,
                        marker: null,
                        setMarker: function (lat, lng) {
                            if (this.marker) mapCreate.removeLayer(this.marker);
                            this.marker = L.marker([lat, lng]).addTo(mapCreate);
                            mapCreate.setView([lat, lng], 15);
                            mapCreate.invalidateSize();
                            $('#lat').val(lat);
                            $('#lng').val(lng);
                        }
                    };
                    mapCreate.on('click', function (e) {
                        if (window.stationMapCreate.marker) mapCreate.removeLayer(window.stationMapCreate.marker);
                        window.stationMapCreate.marker = L.marker(e.latlng).addTo(mapCreate);
                        $('#lat').val(e.latlng.lat);
                        $('#lng').val(e.latlng.lng);
                    });
                }

                var editMapInited = false;
                function initMapEdit() {
                    if (editMapInited || !$('#stationMapEdit').length) return;
                    editMapInited = true;
                    var mapEdit = L.map('stationMapEdit', { center: EGYPT_CENTER, zoom: 6 }).setView(EGYPT_CENTER, 6);
                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '&copy; OpenStreetMap' }).addTo(mapEdit);
                    window.stationMapEdit = {
                        map: mapEdit,
                        marker: null,
                        setMarker: function (lat, lng) {
                            if (this.marker) mapEdit.removeLayer(this.marker);
                            this.marker = L.marker([lat, lng]).addTo(mapEdit);
                            mapEdit.setView([lat, lng], 15);
                            mapEdit.invalidateSize();
                            $('#edit-lat').val(lat);
                            $('#edit-lng').val(lng);
                        }
                    };
                    mapEdit.on('click', function (e) {
                        if (window.stationMapEdit.marker) mapEdit.removeLayer(window.stationMapEdit.marker);
                        window.stationMapEdit.marker = L.marker(e.latlng).addTo(mapEdit);
                        $('#edit-lat').val(e.latlng.lat);
                        $('#edit-lng').val(e.latlng.lng);
                    });
                }

                initMapCreate();
                $('.toggle-btn[data-target-card="#createObjectCard"]').on('click', function () {
                    setTimeout(function () {
                        if (window.stationMapCreate && window.stationMapCreate.map) window.stationMapCreate.map.invalidateSize();
                    }, 350);
                });
                $(document).on('click', '.edit-object', function () {
                    var run = function () {
                        initMapEdit();
                        if (window.stationMapEdit && window.stationMapEdit.map) window.stationMapEdit.map.invalidateSize();
                    };
                    setTimeout(run, 600);
                    setTimeout(run, 1200);
                });

                // Geocode address via Nominatim
                function geocodeAddress(address, latInput, lngInput, mapRef) {
                    if (!address || !address.trim()) return;
                    axios.get('https://nominatim.openstreetmap.org/search', {
                        params: { q: address + ', Egypt', format: 'json', limit: 1 },
                        headers: { 'Accept-Language': 'en' }
                    }).then(function (r) {
                        if (r.data && r.data[0]) {
                            var d = r.data[0];
                            $(latInput).val(d.lat);
                            $(lngInput).val(d.lon);
                            if (mapRef && mapRef.setMarker) mapRef.setMarker(parseFloat(d.lat), parseFloat(d.lon));
                            successToast('@lang("stations.location_updated")');
                        } else {
                            failerToast('@lang("stations.Location") @lang("stations.object_not_found")');
                        }
                    }).catch(function () {
                        failerToast('@lang("stations.object_error")');
                    });
                }
                $('#address').on('blur', function () { geocodeAddress($(this).val(), '#lat', '#lng', window.stationMapCreate); });
                $('#edit-address').on('blur', function () { geocodeAddress($(this).val(), '#edit-lat', '#edit-lng', window.stationMapEdit); });
            }
        })();
    });
</script>
@endpush
