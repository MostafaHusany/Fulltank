@extends('layouts.clients.app')

@push('title')
    <h1 class="h2">@lang('client.vehicles.title')</h1>
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
            <div class="row">
                <div class="col-6 pt-1">
                    @lang('client.vehicles.title')
                </div><!-- /.col-6 -->
                <div class="col-6 text-end">
                    <button class="bulk-delete-btn btn btn-sm btn-outline-dark" data-bs-toggle="tooltip" data-bs-placement="top" title="@lang('layouts.delete')">
                        <i class="fas fa-trash-alt"></i>
                    </button>

                    <button class="relode-btn btn btn-sm btn-outline-dark">
                        <i class="relode-btn-icon fas fa-sync-alt"></i>
                        <span class="relode-btn-loader spinner-grow spinner-grow-sm" style="display: none;" role="status" aria-hidden="true"></span>
                    </button>

                    <button class="btn btn-sm btn-outline-primary toggle-btn" data-current-card="#objectsCard" data-target-card="#createObjectCard">
                        <i class="fas fa-plus"></i>
                    </button>
                </div><!-- /.col-6 -->
            </div><!-- /.row -->
        </div><!-- /.card-header -->

        <div class="card-body custome-table">
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

            <div style="overflow-x: scroll">
                <table id="dataTable" class="table text-center">
                    <thead>
                        <tr>
                            <th>@include('layouts.admin.incs._checkbox_select_all')</th>
                            <th>#</th>
                            <th>@lang('client.vehicles.plate_number')</th>
                            <th>@lang('client.vehicles.model')</th>
                            <th>@lang('client.vehicles.fuel_type')</th>
                            <th>@lang('client.vehicles.quota')</th>
                            <th>@lang('layouts.Active')</th>
                            <th>@lang('layouts.Actions')</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div><!-- /.card-body -->
    </div><!-- /.card -->

    @include('clients.vehicles.incs._create')
    @include('clients.vehicles.incs._edit')
    @include('clients.vehicles.incs._show')

@endsection

@push('custome-js')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script>
    $('document').ready(function () {
        window.is_ar = '{{ $is_ar }}';

        const ROUTES = {
            index               : "{{ route('client.vehicles.index') }}",
            trackingLive        : "{{ route('client.vehicles.tracking.live') }}",
            trackingHistoryTpl  : "{{ route('client.vehicles.tracking.history', ['vehicle' => 999999999]) }}"
        };

        const LANG = {
            active         : '@lang("client.vehicles.active")',
            inactive       : '@lang("client.vehicles.inactive")',
            lastReported   : '@lang("vehicles.Last reported")',
            noFuelVisits   : '@lang("vehicles.No fuel visits")',
            noDailyRoutes  : '@lang("vehicles.No daily routes")'
        };

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
            axios.get(ROUTES.trackingLive).then(function (res) {
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
                    m.bindPopup('<strong>' + (row.plate || '') + '</strong><br><small>' + LANG.lastReported + ': ' + fmtWhen(row.recorded_at) + '</small>');
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

        const objects_dynamic_table = new DynamicTable(
            {
                index_route   : "{{ route('client.vehicles.index') }}",
                store_route   : "{{ route('client.vehicles.store') }}",
                show_route    : "{{ route('client.vehicles.index') }}",
                update_route  : "{{ route('client.vehicles.index') }}",
                destroy_route : "{{ route('client.vehicles.index') }}",
                draft         : {
                    route : '',
                    flag  : ''
                }
            },
            '#dataTable',
            {
                success_el : '#successAlert',
                danger_el  : '#dangerAlert',
                warning_el : '#warningAlert'
            },
            {
                table_id           : '#dataTable',
                toggle_btn         : '.toggle-btn',
                create_obj_btn     : '.create-object',
                update_obj_btn     : '.update-object',
                draft_obj_btn      : '',
                edit_objects_card  : '#editObjectsCard',
                fields_list        : ['id', 'plate_number', 'model', 'fuel_type_id', 'monthly_quota'],
                imgs_fields        : []
            },
            [
                { data: 'checkbox_selector', name: 'checkbox_selector', 'orderable': false },
                { data: 'id',                name: 'id' },
                { data: 'plate_number',      name: 'plate_number' },
                { data: 'model',             name: 'model' },
                { data: 'fuel_type_name', name: 'fuel_type_name', 'orderable': false },
                { data: 'quota_info',        name: 'quota_info', 'orderable': false },
                { data: 'activation',        name: 'activation', 'orderable': false },
                { data: 'actions',           name: 'actions', 'orderable': false }
            ],
            function (d) {}
        );

        objects_dynamic_table.showDataForm = async function (targetBtn) {
            const id = $(targetBtn).data('object-id');
            try {
                const { data, success, msg } = (await axios.get(`${ROUTES.index}/${id}`)).data;
                if (!success) { failerToast(Array.isArray(msg) ? msg[0] : (msg || 'Error')); return false; }

                $('#show-plate_number').text(data.formatted_plate_number || data.plate_number || '---');
                $('#show-model').text(data.model || '---');
                $('#show-fuel_type').text(data.fuel_type_name || '---');
                $('#show-quota').text(data.quota_display || '---');
                $('#show-status').html(data.status === 'active'
                    ? '<span class="badge bg-success">' + LANG.active + '</span>'
                    : '<span class="badge bg-warning text-dark">' + LANG.inactive + '</span>');

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

        objects_dynamic_table.validateData = (data, prefix = '') => {
            let is_valide = true;

            $('.err-msg').slideUp(500);

            if (!data.get('plate_number') || data.get('plate_number') === '') {
                is_valide = false;
                $(`#${prefix}plate_numberErr`).text('@lang("client.vehicles.plate_required")').slideDown(500);
            }

            if (!data.get('fuel_type_id') || data.get('fuel_type_id') === '') {
                is_valide = false;
                $(`#${prefix}fuel_type_idErr`).text('@lang("client.vehicles.fuel_required")').slideDown(500);
            }

            if (!data.get('monthly_quota') || data.get('monthly_quota') === '') {
                is_valide = false;
                $(`#${prefix}monthly_quotaErr`).text('@lang("client.vehicles.quota_required")').slideDown(500);
            }

            return is_valide;
        };

        objects_dynamic_table.addDataToForm = (fields_id_list, imgs_fields, data, prefix) => {
            fields_id_list.forEach(el_id => {
                $(`#${prefix}${el_id}`).val(Boolean(data[el_id]) ? data[el_id] : '').change();
            });

            if (data.quota) {
                $(`#${prefix}monthly_quota`).val(data.quota.amount_limit).change();
            }

            $('#edit-id').val(data.id);
        };

        $('#dataTable').on('change', '.activation-toggle', async function () {
            let target_id = $(this).data('object-id');
            let $toggle = $(this);

            if (!Boolean(target_id)) return -1;

            $(window.loddingSpinnerEl).fadeIn(500);

            try {
                let res = await axios.put(`{{ route('client.vehicles.index') }}/${target_id}`, {
                    _token       : "{{ csrf_token() }}",
                    toggle_status: true
                });

                let { data, success, msg } = res.data;

                if (!success) throw msg;

                successToast(`@lang('client.vehicles.status_updated')`);

            } catch (err) {
                failerToast(typeof(err) == 'string' ? err : `@lang('client.vehicles.error')`);
                $toggle.prop('checked', !$toggle.prop('checked'));
            }

            $(window.loddingSpinnerEl).fadeOut(500);
        });

        $('#dataTable').on('click', '.delete-object', function () {
            let target_id = $(this).data('object-id');
            let object_name = $(this).data('object-name');

            if (!Boolean(target_id)) return -1;

            Swal.fire({
                title: `@lang('client.vehicles.confirm_delete')`,
                text: object_name || '',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: `@lang('layouts.confirm')`,
                cancelButtonText: `@lang('layouts.cancel')`
            }).then(async (result) => {
                if (result.isConfirmed) {
                    $(window.loddingSpinnerEl).fadeIn(500);

                    try {
                        let res = await axios.delete(`{{ route('client.vehicles.index') }}/${target_id}`);

                        let { data, success, msg } = res.data;

                        if (!success) throw msg;

                        successToast(msg || `@lang('client.vehicles.deleted')`);
                        $('.relode-btn').trigger('click');

                    } catch (err) {
                        failerToast(typeof(err) == 'string' ? err : `@lang('client.vehicles.error')`);
                    }

                    $(window.loddingSpinnerEl).fadeOut(500);
                }
            });
        });

        $(document).on('click', '#vehicle-daily-routes-body tr[data-route-id]', function () {
            const rid = $(this).data('route-id');
            selectVehicleDailyRoute(rid);
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

        $(window).on('beforeunload', function () {
            if (liveFleetInterval) clearInterval(liveFleetInterval);
        });

    });
</script>
@endpush
