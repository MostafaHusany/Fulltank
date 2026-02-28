
<div style="display: none" id="createObjectCard" class="card card-body">
    <div class="row">
        <div class="col-6">
            <h5>@lang('client.drivers.create_title')</h5>
        </div>
        <div class="col-6 text-end">
            <div class="toggle-btn btn btn-outline-dark btn-sm" data-current-card="#createObjectCard" data-target-card="#objectsCard">
                <i class="fas fa-times"></i>
            </div>
        </div>
    </div><!-- /.row -->
    <hr/>

    <div>
        <div class="my-3 row">
            <label for="name" class="col-sm-3 col-form-label">@lang('client.drivers.name') <span class="text-danger float-end">*</span></label>
            <div class="col-sm-9">
                <input type="text" class="form-control" id="name" placeholder="@lang('client.drivers.name')">
                <div style="padding: 5px 7px; display: none" id="nameErr" class="err-msg mt-2 alert alert-danger">
                </div>
            </div><!-- /.col-sm-9 -->
        </div><!-- /.my-3 -->

        <div class="my-3 row">
            <label for="phone" class="col-sm-3 col-form-label">@lang('client.drivers.phone') <span class="text-danger float-end">*</span></label>
            <div class="col-sm-9">
                <input type="text" class="form-control" id="phone" placeholder="@lang('client.drivers.phone')">
                <div style="padding: 5px 7px; display: none" id="phoneErr" class="err-msg mt-2 alert alert-danger">
                </div>
            </div><!-- /.col-sm-9 -->
        </div><!-- /.my-3 -->

        <div class="my-3 row">
            <label for="password" class="col-sm-3 col-form-label">@lang('client.drivers.password') <span class="text-danger float-end">*</span></label>
            <div class="col-sm-9">
                <input type="password" class="form-control" id="password" placeholder="@lang('client.drivers.password')">
                <div style="padding: 5px 7px; display: none" id="passwordErr" class="err-msg mt-2 alert alert-danger">
                </div>
            </div><!-- /.col-sm-9 -->
        </div><!-- /.my-3 -->

        <div class="my-3 row">
            <label for="vehicle_id" class="col-sm-3 col-form-label">@lang('client.drivers.vehicle')</label>
            <div class="col-sm-9">
                <select class="form-control" id="vehicle_id">
                    <option value="">@lang('layouts.select')</option>
                    @foreach($vehicles as $vehicle)
                        <option value="{{ $vehicle->id }}">{{ $vehicle->plate_number }}</option>
                    @endforeach
                </select>
                <div style="padding: 5px 7px; display: none" id="vehicle_idErr" class="err-msg mt-2 alert alert-danger">
                </div>
            </div><!-- /.col-sm-9 -->
        </div><!-- /.my-3 -->

        <button class="create-object btn btn-primary float-end">@lang('client.drivers.create_title')</button>
    </div>
</div>

@push('custome-js-2')
<script>
$(document).ready(function () {

    const init = (async () => {

    })();

});
</script>
@endpush
