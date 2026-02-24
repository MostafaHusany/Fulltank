
<div class="card my-3">
    <div class="card-header" id="search-box-container">
        <div class="row">
            <div class="col-3 my-2">
                <div class="form-group search-action">
                    <label for="">@lang('layouts.Gove')</label>
                    <select class="form-control" id="s-gove" data-target="#s-dist"></select>
                </div>
            </div>
            <div class="col-3 my-2">
                <div class="form-group search-action">
                    <label for="">@lang('layouts.Month')</label>
                    <input type="month" class="form-control" id="s-month"></input>
                </div>
            </div>
            <div class="col-3 my-2 text-end">
                <button class="relode-btn btn btn-sm btn-outline-dark">
                    <i class="relode-btn-icon fas fa-sync-alt"></i>
                    <span class="relode-btn-loader spinner-grow spinner-grow-sm" style="display: none;" role="status" aria-hidden="true"></span>
                </button>
            </div>
        </div>
    </div>

    <div class="card-body">
        <div id="map-1"></div>
        
        <div class="form-group mt-3">
            <h3 class="text-primary text-left">
                @lang('layouts.Participants') :
                <span id="participants-count"></span>
            </h3>
        </div><!-- /.form-group -->
    </div><!-- /.card-body -->
</div><!-- /.card -->


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

@push('custome-js')
<script>
$('document').ready(function () {

    function renderMap (mapId) {
        let mapObj = L.map(mapId, {zoomControl : true, zoom: 3, scrollWheelZoom: true, attributionControl: false}).setView([29.957757, 31.164476], 6);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(mapObj);
        mapGroup = L.layerGroup().addTo(mapObj);

        var markers = L.markerClusterGroup();
        // // markers.addLayer(L.marker(getRandomLatLng(mapObj)));
        // mapObj.addLayer(markers);

        return {mapGroup, mapObj};
    }

    function renderPins (mapGroup, data, map) {
        
        mapGroup.clearLayers();

        if (window.markers) {
            map.removeLayer(markers);
        }

        window.markers     = L.markerClusterGroup({
            // spiderfyOnMaxZoom: false,
            // showCoverageOnHover: false,
            // zoomToBoundsOnClick: false
        });

        // var markerGroup = L.layerGroup().addTo(mapGroup);

        data.forEach(record => {
            var marker = L.marker([record.gove.geo_lat, record.gove.geo_lng], {draggable :false});

			markers.addLayer(marker);
        })

        map.addLayer(markers);

    }

    function requestParticipants (filtrAtr, map) {
        
        $('.relode-btn-icon').hide(500);
        $('.relode-btn-loader').show(500);

        axios.get(`{{ route('admin.dashboard.index') }}`, {
            params : {
                get_participants: true,
                ...filtrAtr
            }
        }).then(res => {
            let { data : { participants, gove }, success } = res.data;
            
            if (success) {
                renderPins(map.mapGroup, participants, map.mapObj);
                
                if (Boolean(gove) && participants.length != 1) {
                    map.mapObj.setView([gove.geo_lat, gove.geo_lng], 13);
                } else if (participants.length == 1) {
                    let school = participants[0];
                    map.mapObj.setView([school.gove.geo_lat, school.gove.geo_lng], 18)
                } else {
                    map.mapObj.setView([29.957757, 31.164476], 7)
                }

                $('#participants-count').text(participants.length);
            }
        }).finally(e => {
            $('.relode-btn-icon').show(500);
            $('.relode-btn-loader').hide(500);
        }).catch(e => console.log(e));
    }

    const init = (() => {

        window.markers = null
        window.mapObj  = renderMap('map-1');

        requestParticipants({}, mapObj);

        // START GOVE, DIST SELECT ACTION ...
        $('#s-gove').select2({
            allowClear: true,
            width: '100%',
            placeholder: '@lang("layouts.Select_Governorate")',
            ajax: {
                url: '{{ route("admin.search.districts") }}',
                dataType: 'json',
                delay: 150,
                data: function (params) {
                    return { q: params.term || '', is_main: 1 };
                },
                processResults: function (data) {
                    return {
                        results: $.map(data, function (item) {
                            return {
                                text: (item.ar_name || '') + ' - ' + (item.en_name || ''),
                                id: item.id
                            };
                        })
                    };
                },
                cache: true
            }
        });

        // START SEARCH BOX FILTER
        $('#search-box-container').on('change , keyup', '#s-gove, #s-month, .relode-btn', function () {
            let filtrAtr = {
                gove_id : $('#s-gove').val(),
                month   : $('#s-month').val(),
            };

            requestParticipants(filtrAtr, mapObj);

        });

        $('.relode-btn').click(function () {
            $('.relode-btn').trigger('change');
        });

        
    })();
});
</script>
@endpush