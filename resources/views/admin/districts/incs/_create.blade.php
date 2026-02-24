
<div style="display: none" id="createObjectCard" class="card card-body">
    <div class="row">
        <div class="col-6">
            <h5>@lang('districts.Create Title')</h5>
        </div>
        <div class="col-6 text-end">
            <div class="toggle-btn btn btn-outline-dark btn-sm" data-current-card="#createObjectCard" data-target-card="#objectsCard">
                <i class="fas fa-times"></i>
            </div>
        </div>
    </div><!-- /.row -->
    <hr/>

    <form action="/" id="objectForm">
        
        {{--
        <div class="my-3 row">
            <label for="dreafted_data" class="col-sm-2 col-form-label">@lang('drafts.Drafted Data')</label>
            <div class="col-sm-10">
                <select class="form-control" id="dreafted_data"></select>
            </div>
        </div><!-- /.my-3 -->
        --}}

        <div class="my-3 row">
            <input type="hidden" id="geo_lat" disabled="disabled">
            <input type="hidden" id="geo_lng" disabled="disabled">

            <div class="col-sm-12">
                <div id="map" style="border: 1px solid #ddd; height: 250px;"></div>
                <div style="padding: 5px 7px; display: none" id="geo_latErr" class="err-msg mt-2 alert alert-danger"></div>
            </div>
        </div><!-- /.my-3 -->
        
        <div class="my-3 row">
            <label for="name" class="col-sm-2 col-form-label">@lang('districts.Governorate') <span class="text-danger float-end">*</span></label>
            
            <div class="col-5">
                <input type="text" class="form-control custome-en-field" id="en_name" placeholder="Governorate name in english">
                <div style="padding: 5px 7px; display: none" id="en_nameErr" class="err-msg mt-2 alert alert-danger custome-en-field">
                </div>
            </div><!-- /.col-5 -->
            
            <div class="col-5">
                <input type="text" class="form-control custome-ar-field" id="ar_name" placeholder="أسم المحافظة بالعربية">
                <div style="padding: 5px 7px; display: none" id="ar_nameErr" class="err-msg mt-2 alert alert-danger custome-ar-field">
                </div>
            </div><!-- /.col-5 -->
        </div><!-- /.my-3 -->

        <button class="create-object btn btn-primary float-end">@lang('districts.Create Title')</button>

        <button class="create-draft btn btn-secondary float-end mx-2">@lang('drafts.Save Draft')</button>
    </form>
</div>

@push('custome-js-2')
<script>
$(document).ready(function () {
    const Create_form_custome_functions = (function () {
        let map              = null;
        var markerGroup      = null;
        let timeoutContainer = null;

        window.gove_id       = null;
        window.parents_id    = [];

        function starter_event () {
            $('.toggle-btn').click(function () {
                let target_card = $(this).data('target-card');

                if (target_card === '#createObjectCard') {
                    map !== null && map.remove();
                    $('#latitude, #longitude').val('');
                    
                    setTimeout(() => {    
                        map = L.map('map', {zoomControl : true, zoom: 3, scrollWheelZoom: true, attributionControl: false}).setView([30.012086, 31.215356], 7);
                        
                        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {}).addTo(map);

                        markerGroup = L.layerGroup().addTo(map);

                        map.on('click', function (e) {
                            let {lat, lng} = e.latlng;
                            
                            $('#geo_lat').val(lat)
                            $('#geo_lng').val(lng)
                            
                            markerGroup.clearLayers();
                            L.marker([lat, lng], {draggable :true}).addTo(markerGroup);
                        });

                    }, 1000);
                }
            });

            $('#ar_name').on('keyup', function () {
                let address_val = $(this).val();

                clearInterval(timeoutContainer);

                timeoutContainer = setTimeout(() => {

                    axios.post(`https://maps.googleapis.com/maps/api/geocode/json?address=${address_val}&key={{ ENV('GOOGLE_MAPS_KEYS') }}`)
                    .then(res => {
                        if (Boolean(res.data.results) && res.data.results.length) {
                            let { lat, lng } = res.data.results[0].geometry.location
                        
                            markerGroup.clearLayers();

                            L.marker([lat, lng], {draggable :true}).addTo(markerGroup);
                            
                            map.setView([lat, lng], 10);
                            
                            $('#geo_lat').val(lat)
                            $('#geo_lng').val(lng)
                        }
                    });

                }, 500);
                
            });
        }// end :: starter_event

        return {
            starter_event : starter_event
        }
    })();

    Create_form_custome_functions.starter_event();
});
</script>
@endpush