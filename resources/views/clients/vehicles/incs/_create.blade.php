
<div style="display: none" id="createObjectCard" class="card card-body">
    <div class="row">
        <div class="col-6">
            <h5>@lang('client.vehicles.create_title')</h5>
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
            <label for="plate_number" class="col-sm-3 col-form-label">@lang('client.vehicles.plate_number') <span class="text-danger float-end">*</span></label>
            <div class="col-sm-9">
                <input type="text" class="form-control" id="plate_number" placeholder="@lang('client.vehicles.plate_number')">
                <div style="padding: 5px 7px; display: none" id="plate_numberErr" class="err-msg mt-2 alert alert-danger">
                </div>
            </div><!-- /.col-sm-9 -->
        </div><!-- /.my-3 -->

        <div class="my-3 row">
            <label for="model" class="col-sm-3 col-form-label">@lang('client.vehicles.model')</label>
            <div class="col-sm-9">
                <input type="text" class="form-control" id="model" placeholder="@lang('client.vehicles.model')">
                <div style="padding: 5px 7px; display: none" id="modelErr" class="err-msg mt-2 alert alert-danger">
                </div>
            </div><!-- /.col-sm-9 -->
        </div><!-- /.my-3 -->

        <div class="my-3 row">
            <label for="fuel_type_id" class="col-sm-3 col-form-label">@lang('client.vehicles.fuel_type') <span class="text-danger float-end">*</span></label>
            <div class="col-sm-9">
                <select class="form-control" id="fuel_type_id">
                    <option value="">@lang('layouts.select')</option>
                    @foreach($fuelTypes ?? [] as $fuelType)
                        <option value="{{ $fuelType->id }}">{{ $fuelType->name }}</option>
                    @endforeach
                </select>
                <div style="padding: 5px 7px; display: none" id="fuel_type_idErr" class="err-msg mt-2 alert alert-danger">
                </div>
            </div><!-- /.col-sm-9 -->
        </div><!-- /.my-3 -->

        <div class="my-3 row">
            <label for="monthly_quota" class="col-sm-3 col-form-label">@lang('client.vehicles.monthly_quota') <span class="text-danger float-end">*</span></label>
            <div class="col-sm-9">
                <input type="number" step="0.01" min="0" class="form-control" id="monthly_quota" placeholder="@lang('client.vehicles.monthly_quota')">
                <div style="padding: 5px 7px; display: none" id="monthly_quotaErr" class="err-msg mt-2 alert alert-danger">
                </div>
            </div><!-- /.col-sm-9 -->
        </div><!-- /.my-3 -->

        <button class="create-object btn btn-primary float-end">@lang('client.vehicles.create_title')</button>
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
