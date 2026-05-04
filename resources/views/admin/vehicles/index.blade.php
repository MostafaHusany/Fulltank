@extends('layouts.admin.app')

@push('title')
    <h1 class="h2">@lang('vehicles.Title Administration')</h1>
@endpush

@push('custome-css')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="">
<style>
    .vehicle-admin-map { height: 320px; z-index: 1; }
    #vehicles-live-map { height: 360px; z-index: 1; }
    #vehicle-daily-route-map { height: 340px; z-index: 1; }
</style>
@endpush

@section('content')
    <div id="objectsCard" class="card">
        <div class="card-header">
            <div class="row g-2 align-items-center">
                <div class="col-12 col-md-6 pt-1 order-2 order-md-1">
                    <span class="fw-bold">@lang('vehicles.Title Administration')</span>
                </div>
                <div class="col-12 col-md-6 order-1 order-md-2">
                    <div class="d-flex flex-wrap gap-1 justify-content-end">
                        @if($permissions == 'admin' || in_array('vehicles_delete', $permissions))
                        <button class="bulk-delete-btn btn btn-sm btn-outline-dark" title="@lang('layouts.delete')">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                        @endif
                        <button class="relode-btn btn btn-sm btn-outline-dark" title="@lang('clients.object_updated')">
                            <i class="relode-btn-icon fas fa-sync-alt"></i>
                            <span class="relode-btn-loader spinner-grow spinner-grow-sm" style="display: none;" role="status" aria-hidden="true"></span>
                        </button>
                        <button class="btn btn-sm btn-outline-dark toggle-search" title="@lang('layouts.show')">
                            <i class="fas fa-search"></i>
                        </button>
                        @if($permissions == 'admin' || in_array('vehicles_add', $permissions))
                        <button class="btn btn-sm btn-outline-primary toggle-btn" data-current-card="#objectsCard" data-target-card="#createObjectCard" title="@lang('vehicles.Create Vehicle')">
                            <i class="fas fa-plus"></i>
                        </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="card-body custome-table p-2 p-md-3">
            @include('admin.vehicles.incs._search')

            <div class="mb-3 p-2 p-md-3 border rounded bg-light">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
                    <div>
                        <h6 class="mb-0 fw-semibold">@lang('vehicles.Live Fleet Map')</h6>
                        <div class="text-muted small">@lang('vehicles.Live map hint')</div>
                    </div>
                    <span class="badge bg-secondary" id="vehicles-live-map-updated">—</span>
                </div>
                <div id="vehicles-live-map" class="border rounded bg-white"></div>
            </div>

            <div class="table-responsive">
            <table id="dataTable" class="table table-sm table-hover text-center mb-0">
                <thead>
                    <tr>
                        <th>@include('layouts.admin.incs._checkbox_select_all')</th>
                        <th>#</th>
                        <th>@lang('vehicles.Plate Number')</th>
                        <th>@lang('vehicles.Client')</th>
                        <th>@lang('vehicles.Model')</th>
                        <th>@lang('vehicles.Fuel Type')</th>
                        <th>@lang('layouts.Active')</th>
                        <th>@lang('layouts.Actions')</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
            </div>
        </div>
    </div><!-- /.card -->

    @if($permissions == 'admin' || in_array('vehicles_add', $permissions))
        @include('admin.vehicles.incs._create')
    @endif

    @if($permissions == 'admin' || in_array('vehicles_show', $permissions))
        @include('admin.vehicles.incs._show')
    @endif

    @if($permissions == 'admin' || in_array('vehicles_edit', $permissions))
        @include('admin.vehicles.incs._edit')
    @endif

@endSection

@push('custome-js')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script>
    (function () {
        var filterClientId   = @json($filterClientId ?? null);
        var filterClientName = @json($filterClientName ?? null);

        const ROUTES = {
            index            : "{{ route('admin.vehicles.index') }}",
            store            : "{{ route('admin.vehicles.store') }}",
            clients          : "{{ route('admin.search.clients') }}",
            trackingLive     : "{{ route('admin.vehicles.tracking.live') }}",
            trackingHistoryTpl: "{{ route('admin.vehicles.tracking.history', ['vehicle' => 999999999]) }}"
        };

        const LANG = {
            client_required   : '@lang("vehicles.client_required")',
            plate_required      : '@lang("vehicles.plate_number_required")',
            model_required      : '@lang("vehicles.model_required")',
            fuel_required       : '@lang("vehicles.fuel_type_required")',
            active              : '@lang("layouts.active")',
            inactive            : '@lang("layouts.de-active")',
            selectClient        : '{{ __("vehicles.Select Client") }}',
            lastReported        : '{{ __("vehicles.Last reported") }}',
            noFuelVisits        : '{{ __("vehicles.No fuel visits") }}',
            noDailyRoutes       : '{{ __("vehicles.No daily routes") }}'
        };

        const VALIDATION = { client_id: LANG.client_required, plate_number: LANG.plate_required, model: LANG.model_required, fuel_type_id: LANG.fuel_required };

        let liveFleetMap = null;
        let liveFleetLayer = null;
        let liveFleetInterval = null;
        let vehicleDailyRouteMap = null;
        let vehicleDailyRouteLayer = null;
        let vehicleDailyRoutePolyline = null;
        let vehicleRoutesLocationsById = {};

        function trackingHistoryUrl(vehicleId) {
            return ROUTES.trackingHistoryTpl.replace('999999999', String(vehicleId));
        }

        function fmtWhen(iso) {
            if (!iso) return '—';
            try {
                const d = new Date(iso);
                return isNaN(d.getTime()) ? iso : d.toLocaleString();
            } catch (e) { return iso; }
        }

        function initLiveFleetMap() {
            if (typeof L === 'undefined' || !document.getElementById('vehicles-live-map')) return;
            if (liveFleetMap) return;
            liveFleetMap = L.map('vehicles-live-map', { scrollWheelZoom: false }).setView([30.0444, 31.2357], 11);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; OpenStreetMap'
            }).addTo(liveFleetMap);
            liveFleetLayer = L.layerGroup().addTo(liveFleetMap);
        }

        function refreshLiveFleetMap() {
            if (!liveFleetMap || !liveFleetLayer) return;
            const params = {};
            if (filterClientId) params.client_id = filterClientId;
            else if ($('#s-client_id').length && $('#s-client_id').val()) params.client_id = $('#s-client_id').val();

            axios.get(ROUTES.trackingLive, { params: params }).then(function (res) {
                const payload = res.data || {};
                if (!payload.success) {
                    if (payload.msg) failerToast(typeof payload.msg === 'string' ? payload.msg : 'Error');
                    return;
                }
                const rows = payload.data || [];
                liveFleetLayer.clearLayers();
                const bounds = [];
                rows.forEach(function (row) {
                    if (row.lat == null || row.lng == null) return;
                    const color = row.status === 'active' ? '#198754' : '#6c757d';
                    const m = L.circleMarker([row.lat, row.lng], { radius: 8, color: color, fillColor: color, fillOpacity: 0.85, weight: 2 });
                    m.bindPopup('<strong>' + (row.plate || '') + '</strong><br>' + (row.client_name || '') + '<br><small>' + LANG.lastReported + ': ' + fmtWhen(row.recorded_at) + '</small>');
                    m.addTo(liveFleetLayer);
                    bounds.push([row.lat, row.lng]);
                });
                if (bounds.length) {
                    liveFleetMap.fitBounds(bounds, { padding: [28, 28], maxZoom: 13 });
                }
                $('#vehicles-live-map-updated').text(new Date().toLocaleTimeString());
                setTimeout(function () { liveFleetMap.invalidateSize(); }, 200);
            }).catch(function () {
                $('#vehicles-live-map-updated').text('—');
            });
        }

        function ensureDailyRouteMap() {
            if (typeof L === 'undefined' || !document.getElementById('vehicle-daily-route-map')) return false;
            if (vehicleDailyRouteMap) return true;
            vehicleDailyRouteMap = L.map('vehicle-daily-route-map', { scrollWheelZoom: false }).setView([30.0444, 31.2357], 12);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; OpenStreetMap'
            }).addTo(vehicleDailyRouteMap);
            vehicleDailyRouteLayer = L.layerGroup().addTo(vehicleDailyRouteMap);
            return true;
        }

        function clearDailyRouteMap() {
            if (!vehicleDailyRouteMap || !vehicleDailyRouteLayer) return;
            vehicleDailyRouteLayer.clearLayers();
            if (vehicleDailyRoutePolyline) {
                vehicleDailyRouteMap.removeLayer(vehicleDailyRoutePolyline);
                vehicleDailyRoutePolyline = null;
            }
        }

        function selectVehicleDailyRoute(routeId) {
            if (!ensureDailyRouteMap()) return;
            clearDailyRouteMap();
            const key = String(routeId);
            const locs = vehicleRoutesLocationsById[key] || [];
            const latlngs = locs.map(function (p) { return [p.lat, p.lng]; });

            if (latlngs.length > 1) {
                vehicleDailyRoutePolyline = L.polyline(latlngs, { color: '#0d6efd', weight: 4, opacity: 0.88 }).addTo(vehicleDailyRouteMap);
                vehicleDailyRouteMap.fitBounds(latlngs, { padding: [26, 26], maxZoom: 14 });
            } else if (latlngs.length === 1) {
                L.circleMarker(latlngs[0], { radius: 9, color: '#0d6efd', fillColor: '#0d6efd', fillOpacity: 0.9 }).addTo(vehicleDailyRouteLayer);
                vehicleDailyRouteMap.setView(latlngs[0], 13);
            } else {
                vehicleDailyRouteMap.setView([30.0444, 31.2357], 11);
            }

            setTimeout(function () { if (vehicleDailyRouteMap) vehicleDailyRouteMap.invalidateSize(); }, 250);
        }

        function loadVehicleShowTracking(vehicleId) {
            vehicleRoutesLocationsById = {};

            axios.get(trackingHistoryUrl(vehicleId)).then(function (res) {
                const payload = res.data || {};
                if (!payload.success) {
                    if (payload.msg) failerToast(typeof payload.msg === 'string' ? payload.msg : 'Error');
                    return;
                }
                const pack = payload.data || {};
                const trips = pack.fuel_visits || [];
                const dailyRoutes = pack.daily_routes || [];
                vehicleRoutesLocationsById = pack.locations_by_daily_route || {};

                const $tb = $('#vehicle-show-trips-body');
                $tb.empty();
                if (!trips.length) {
                    $tb.append('<tr><td colspan="5" class="text-muted text-center py-3">' + LANG.noFuelVisits + '</td></tr>');
                } else {
                    trips.forEach(function (t) {
                        $tb.append(
                            '<tr><td>' + fmtWhen(t.completed_at) + '</td>' +
                            '<td>' + (t.station_name || '—') + '</td>' +
                            '<td>' + (t.driver_name || '—') + '</td>' +
                            '<td>' + (t.actual_liters != null ? Number(t.actual_liters).toFixed(3) : '—') + '</td>' +
                            '<td>' + (t.total_amount != null ? Number(t.total_amount).toFixed(2) : '—') + '</td></tr>'
                        );
                    });
                }

                const $rb = $('#vehicle-daily-routes-body');
                $rb.empty();
                if (!dailyRoutes.length) {
                    $rb.append('<tr><td colspan="4" class="text-muted text-center py-3">' + LANG.noDailyRoutes + '</td></tr>');
                    clearDailyRouteMap();
                } else {
                    dailyRoutes.forEach(function (r, idx) {
                        const win = (r.started_at && r.ended_at) ? (fmtWhen(r.started_at) + ' – ' + fmtWhen(r.ended_at)) : '—';
                        const dist = r.distance_km != null ? Number(r.distance_km).toFixed(2) : '—';
                        $rb.append(
                            '<tr style="cursor:pointer" data-route-id="' + r.id + '" class="' + (idx === 0 ? 'table-primary' : '') + '">' +
                            '<td>' + (r.route_date || '—') + '</td>' +
                            '<td>' + (r.point_count != null ? r.point_count : '—') + '</td>' +
                            '<td>' + dist + '</td>' +
                            '<td class="small">' + win + '</td></tr>'
                        );
                    });
                    selectVehicleDailyRoute(dailyRoutes[0].id);
                }

                setTimeout(function () { if (vehicleDailyRouteMap) vehicleDailyRouteMap.invalidateSize(); }, 550);
            }).catch(function (err) {
                const msg = err.response?.data?.msg;
                failerToast(Array.isArray(msg) ? msg[0] : (msg || 'Error'));
            });
        }

        $('document').ready(function () {

            const objects_dynamic_table = new DynamicTable(
                {
                    index_route   : ROUTES.index,
                    store_route   : ROUTES.store,
                    show_route    : ROUTES.index,
                    update_route  : ROUTES.index,
                    destroy_route : ROUTES.index,
                    draft         : { route: '', flag: '' }
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
                    fields_list     : ['id', 'client_id', 'plate_number', 'model', 'fuel_type_id'],
                    imgs_fields     : []
                },
                [
                    { data: 'checkbox_selector',  name: 'checkbox_selector', orderable: false },
                    { data: 'id',                 name: 'id' },
                    { data: 'formatted_plate',    name: 'formatted_plate' },
                    { data: 'client_name',        name: 'client_name' },
                    { data: 'model',              name: 'model' },
                    { data: 'fuel_type_name',     name: 'fuel_type_name' },
                    { data: 'activation',         name: 'activation' },
                    { data: 'actions',            name: 'actions' },
                ],
                function (d) {
                    if (filterClientId) d.client_id = filterClientId;
                    else if ($('#s-client_id').length) d.client_id = $('#s-client_id').val();
                    if ($('#s-plate_number').length) d.plate_number = $('#s-plate_number').val();
                    if ($('#s-model').length) d.model = $('#s-model').val();
                    if ($('#s-fuel_type_id').length) d.fuel_type_id = $('#s-fuel_type_id').val();
                    if ($('#s-status').length) d.status = $('#s-status').val();
                }
            );

            objects_dynamic_table.validateData = (data, prefix = '') => {
                let valid = true;
                $('.err-msg').slideUp(500);

                Object.keys(VALIDATION).forEach(field => {
                    const val = data.get(field);
                    if (!val || val === '') {
                        valid = false;
                        $(`#${prefix}${field}Err`).text(VALIDATION[field]).slideDown(500);
                    }
                });
                return valid;
            };

            objects_dynamic_table.showDataForm = async (targetBtn) => {
                const id = $(targetBtn).data('object-id');
                try {
                    const { data, success, msg } = (await axios.get(`${ROUTES.index}/${id}`)).data;
                    if (!success) { failerToast(Array.isArray(msg) ? msg[0] : (msg || 'Error')); return false; }

                    $('#show-plate_number').text(data.formatted_plate_number || '---');
                    $('#show-client_name').text(data.client_name || '---');
                    $('#show-model').text(data.model || '---');
                    $('#show-fuel_type').text(data.fuel_type_name || '---');
                    $('#show-status').html(data.status === 'active' ? `<span class="badge bg-success">${LANG.active}</span>` : `<span class="badge bg-warning">${LANG.inactive}</span>`);
                    const detBtn = document.getElementById('vehicle-tab-details-btn');
                    if (detBtn && window.bootstrap && window.bootstrap.Tab) {
                        window.bootstrap.Tab.getOrCreateInstance(detBtn).show();
                    }
                    if ($('#vehicle-daily-route-map').length) {
                        loadVehicleShowTracking(id);
                    }
                    return true;
                } catch (err) {
                    const msg = err.response?.data?.msg || (typeof err === 'string' ? err : (Array.isArray(err) ? err[0] : 'Error'));
                    failerToast(Array.isArray(msg) ? msg[0] : msg);
                    return false;
                }
            };

            objects_dynamic_table.addDataToForm = (fields_id_list, imgs_fields, data, prefix) => {
                fields_id_list.forEach(el_id => {
                    if (el_id !== 'client_id') $(`#${prefix}${el_id}`).val(data[el_id] ?? '').change();
                });
                if (prefix === 'edit-' && data.client_id) {
                    const $sel = $(`#${prefix}client_id`);
                    $sel.empty().append(new Option(data.client_name || `Client #${data.client_id}`, data.client_id, true, true)).trigger('change');
                }
                $(`#${prefix}id`).val(data.id);
            };

            (() => {
                const clientsSelect2Opts = {
                    allowClear: true,
                    width: '100%',
                    placeholder: LANG.selectClient,
                    ajax: {
                        url: ROUTES.clients,
                        dataType: 'json',
                        delay: 250,
                        data: function (params) { return { q: params.term || '' }; },
                        processResults: function (data) {
                            return {
                                results: (data || []).map(function (item) {
                                    return { id: item.id, text: (item.company_name || item.name) + (item.phone ? ' - ' + item.phone : '') };
                                })
                            };
                        },
                        cache: true
                    },
                    minimumInputLength: 0
                };

                $('#client_id').select2(clientsSelect2Opts);
                $('#edit-client_id').select2(clientsSelect2Opts);

                $(document).on('click', '.toggle-btn[data-target-card="#createObjectCard"]', function () {
                    if (filterClientId && filterClientName) {
                        var $sel = $('#client_id');
                        if ($sel.length) {
                            $sel.empty().append(new Option(filterClientName, filterClientId, true, true)).trigger('change');
                        }
                    }
                });

                if (filterClientId && filterClientName) {
                    $('.search-container').show(500);
                }

                axios.get(ROUTES.clients, { params: { q: '' } }).then(function (res) {
                    var data = res.data || [];
                    var $sel = $('#s-client_id');
                    $sel.find('option:not(:first)').remove();
                    data.forEach(function (item) {
                        $sel.append(new Option((item.company_name || item.name) + (item.phone ? ' - ' + item.phone : ''), item.id));
                    });
                    if (filterClientId && filterClientName) {
                        if (!$sel.find('option[value="' + filterClientId + '"]').length) {
                            $sel.append(new Option(filterClientName, filterClientId));
                        }
                        $sel.val(filterClientId).trigger('change');
                    }
                });

                $(document).on('click', '.vehicle-history-btn', function (e) {
                    e.preventDefault();
                    const id = $(this).data('vehicle-id');
                    const $show = $(`.show-object[data-object-id="${id}"]`).first();
                    if ($show.length) {
                        $show.trigger('click');
                    }
                });

                $(document).on('click', '#vehicle-daily-routes-body tr[data-route-id]', function () {
                    const id = $(this).data('route-id');
                    selectVehicleDailyRoute(id);
                    $('#vehicle-daily-routes-body tr[data-route-id]').removeClass('table-primary');
                    $(this).addClass('table-primary');
                });

                const routesTabBtn = document.getElementById('vehicle-tab-routes-btn');
                if (routesTabBtn) {
                    routesTabBtn.addEventListener('shown.bs.tab', function () {
                        setTimeout(function () {
                            if (vehicleDailyRouteMap) vehicleDailyRouteMap.invalidateSize();
                        }, 200);
                    });
                }

                initLiveFleetMap();
                refreshLiveFleetMap();
                if (liveFleetMap) {
                    liveFleetInterval = setInterval(refreshLiveFleetMap, 15000);
                }

                $('.search-action').on('keyup change', function () {
                    clearTimeout(window._vehLiveSearchT);
                    window._vehLiveSearchT = setTimeout(refreshLiveFleetMap, 600);
                });
                $('#s-client_id').on('change', function () {
                    refreshLiveFleetMap();
                });
            })();

            $(window).on('beforeunload', function () {
                if (liveFleetInterval) clearInterval(liveFleetInterval);
            });

        });
    })();
</script>
@endpush
