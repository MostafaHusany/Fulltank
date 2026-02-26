
<div style="display: none" id="manageBusCard" class="card card-body">
    <div class="row">
        <div class="col-6">
            <h5>@lang('admission_students.Manage_Bus')</h5>
        </div>
        <div class="col-6 text-end">
            <div class="toggle-btn btn btn-outline-dark btn-sm" data-current-card="#manageBusCard" data-target-card="#objectsCard">
                <i class="fas fa-times"></i>
            </div>
        </div>
    </div><!-- /.row -->
    <hr/>

    <div>

        <input type="hidden" id="map-id" value="">
        
        <div class="my-3">
            <div id="map-1"></div>
        </div>

        <div class="my-2 row">
            <label for="map-bus_address" class="col-sm-2 col-form-label">@lang('admission_students.address') <span class="text-danger float-end">*</span></label>
            <div class="col-sm-10">
                <input type="text" class="form-control" id="map-bus_address" placeholder="@lang('admission_students.map-bus_address')">
                <div style="padding: 5px 7px; display: none" id="map-bus_addressErr" class="err-msg mt-2 alert alert-danger">
                </div>
            </div><!-- /.col-sm-10 -->
        </div><!-- /.my-2 -->

        
        <div class="my-2 row">
            <label for="map-bus_lat" class="col-sm-2 col-form-label">@lang('admission_students.Location') <span class="text-danger float-end">*</span></label>
            <div class="col-sm-5">
                <input type="number" class="form-control" id="map-bus_lat" placeholder="@lang('admission_students.map-bus_lat')">
                <div style="padding: 5px 7px; display: none" id="map-bus_latErr" class="err-msg mt-2 alert alert-danger">
                </div>
            </div><!-- /.col-sm-5 -->

            <div class="col-sm-5">
                <input type="number" class="form-control" id="map-bus_lng" placeholder="@lang('admission_students.map-bus_lng')">
                <div style="padding: 5px 7px; display: none" id="map-bus_lngErr" class="err-msg mt-2 alert alert-danger">
                </div>
            </div><!-- /.col-sm-5 -->
        </div><!-- /.my-2 -->

        <button id="manage-bus-data" class="btn btn-warning float-end">@lang('admission_students.Manage_Bus')</button>
        
    </div>
</div>

@push('custome-plugin')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

<!-- After Leaflet script -->
<link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.css" />
<link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.Default.css" />
<script src="https://unpkg.com/leaflet.markercluster@1.5.3/dist/leaflet.markercluster.js"></script>

<style>
    #map-1  {
        height: 400px;
    }
</style>
@endpush

@push('custome-js-2')
<script>
$(document).ready(function () {  
    
    let map = null, mapGroup = null;

    const View = (() => {
        const renders = {
            renderMap () {
                if (Boolean(map) && Boolean(mapGroup)) return -1

                map = L.map('map-1', {
                    zoomControl: true,
                    zoom: 6,
                    scrollWheelZoom: true,
                    attributionControl: false
                }).setView([29.957757, 31.164476], 8);

                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);

                mapGroup = L.layerGroup().addTo(map);
            },

            renderPins(coords) {
                mapGroup.clearLayers();

                if (window.markers) {
                    map.removeLayer(window.markers);
                }

                window.markers = L.markerClusterGroup();

                coords.forEach(({ latitude, longitude }) => {
                    let marker = L.marker([latitude, longitude], { draggable: true });

                    marker.on('dragend', function (e) {
                        const { lat, lng } = e.target.getLatLng();
                        console.log("📍 New coordinates:", lat, lng);

                        // Example: fill inputs or trigger action
                        $('#map-bus_lat').val(lat);
                        $('#map-bus_lng').val(lng);
                    });
                    window.markers.addLayer(marker);
                });

                map.addLayer(window.markers);
            },

            toggleBtn (targetObj, start = true) {
                if (start) {
                    $(loddingSpinnerEl).fadeIn(500);
                    $(targetObj).attr('disabled', 'disabled');
                } else {
                    $(loddingSpinnerEl).fadeOut(500);
                    $(targetObj).removeAttr('disabled');
                }
            },

            toggleForm (open = true) {
                if (open) {
                    $('#objectsCard').slideUp(500);
                    $('#manageBusCard').slideDown(500);
                } else {
                    $('#objectsCard').slideDown(500);
                    $('#manageBusCard').slideUp(500);
                }
            }
        };

        return {
            renders
        }
    })();

    const init = async () => {
        const { renders } = View;

        window.markers         = null;
        let mapRequestInterval = null;

        $('#dataTable').on('click', '.manage-bus', async function () {
            let target_id   = $(this).data('target');
            let fields      = ['id', 'bus_address', 'bus_lat', 'bus_lng'];

            renders.toggleBtn(this, true);

            try {
                let res = await axios.get(`{{ route('admin.admissionStudents.index') }}/${target_id}`);

                let { data, success, msg } = res.data;

                if (!success) throw msg;

                fields.forEach(field => {
                    $(`#map-${field}`).val(data[field]);
                });

                renders.toggleForm();
                renders.renderMap()

                if (Boolean(data.bus_lat) && Boolean(data.bus_lng))
                renders.renderPins([{ latitude : data.bus_lat, longitude : data.bus_lng }]);
                else
                renders.renderPins([]);

            } catch (err) {
                failerToast(err);
            }
            
            renders.toggleBtn(this, false);
        });

        $('#map-bus_address').on('keyup', function () {
            let address = $(this).val();
            if (!address) return;

            if (mapRequestInterval) clearTimeout(mapRequestInterval);

            mapRequestInterval = setTimeout(async () => {
                try {
                    let res = await axios.get(`{{ route('getLocation') }}`, {
                        params: { address }
                    });

                    let { latitude, longitude } = res.data;

                    console.log('Location:', latitude, longitude);
                    
                    $('#map-bus_lat').val(latitude);
                    $('#map-bus_lng').val(longitude);

                    renders.renderPins([{ latitude, longitude }]);
                } catch (err) {
                    console.error('Location error', err);
                }
            }, 1000);
        });

        $('#manage-bus-data').on('click', async function () {
            let formData  = {};
            let fields    = ['bus_address', 'bus_lat', 'bus_lng'];
            let target_id = $('#map-id').val();

            let is_valied = true;

            fields.forEach(field => {
                let tmp = $(`#map-${field}`).val();

                if (!Boolean(tmp)) {
                    is_valied = false;
                    $(`#map-${field}`).css('border-color', 'red');
                } else {
                    formData[field] = tmp;
                    $(`#map-${field}`).css('border-color', '');
                }
            });

            if (!is_valied) return -1;

            renders.toggleBtn(this);

            try {
                let res = await axios.post(`{{ route('admin.admissionStudents.index') }}/${target_id}`, {
                    ...formData,
                    _token      : "{{ csrf_token() }}",
                    _method     : "PUT",
                    bus_data    : true
                });
                let { data, success, msg } = res.data;

                if (!success) throw msg;

                successToast(msg);

            } catch (err) {
                failerToast(err);
            }
            
            renders.toggleForm(false);
            renders.toggleBtn(this, false);
        });
    };

    init();

});
</script>
@endpush