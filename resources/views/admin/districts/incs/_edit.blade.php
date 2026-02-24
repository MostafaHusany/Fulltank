<div style="display: none" id="editObjectsCard" class="card card-body">
    <div class="row">
        <div class="col-6">
            <h5>@lang('districts.Update Title')</h5>
        </div>
        
        <div class="col-6 text-end">
            <div class="toggle-btn btn btn-outline-dark btn-sm" data-current-card="#editObjectsCard" data-target-card="#objectsCard">
                <i class="fas fa-times"></i>
            </div>
        </div>
    </div><!-- /.row -->
    <hr/>

    <form action="/" id="objectForm">
        <input type="hidden" id="edit-id">

        <div class="my-3 row">
            <input type="hidden" id="edit-geo_lat" disabled="disabled">
            <input type="hidden" id="edit-geo_lng" disabled="disabled">

            <div class="col-sm-12">
                <div id="edit-map" style="border: 1px solid #ddd; height: 200px;"></div>
                <div style="padding: 5px 7px; display: none" id="edit-LocationErr" class="err-msg mt-2 alert alert-danger">
                <div style="padding: 5px 7px; display: none" id="edit-geo_latErr" class="err-msg mt-2 alert alert-danger"></div>
                <div style="padding: 5px 7px; display: none" id="edit-geo_lngErr" class="err-msg mt-2 alert alert-danger"></div>
                </div>
            </div>
        </div><!-- /.my-3 -->
        
        <div class="my-3 row">
            <label for="name" class="col-sm-2 col-form-label">@lang('districts.Governorate') <span class="text-danger float-end">*</span></label>
            
            <div class="col-5">
                <input type="text" class="form-control custome-en-field" id="edit-en_name" placeholder="Governorate name in english">
                <div style="padding: 5px 7px; display: none" id="edit-en_nameErr" class="err-msg mt-2 alert alert-danger custome-en-field">
                </div>
            </div><!-- /.col-5 -->

            <div class="col-5">
                <input type="text" class="form-control custome-ar-field" id="edit-ar_name" placeholder="أسم المحافظة بالعربية">
                <div style="padding: 5px 7px; display: none" id="edit-ar_nameErr" class="err-msg mt-2 alert alert-danger custome-ar-field">
                </div>
            </div><!-- /.col-5 -->
        </div><!-- /.my-3 -->

        <button class="update-object btn btn-warning float-end">@lang('districts.Update Title')</button>
    </form>
</div>

@push('custome-js')
<script>
$(document).ready(function () {
    let timeoutContainer = null;

    $('#edit-ar_name').on('keyup', function () {
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
                            
                            $('#edit-geo_lat').val(lat)
                            $('#edit-geo_lng').val(lng)
                        }
                    });

                }, 500);
        
    });
});
</script>
@endpush