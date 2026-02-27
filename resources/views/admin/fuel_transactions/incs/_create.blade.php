<div style="display: none" id="createObjectCard" class="card card-body">
    <div class="row">
        <div class="col-6">
            <h5>@lang('fuel_transactions.Manual Entry')</h5>
        </div>
        <div class="col-6 text-end">
            <div class="toggle-btn btn btn-outline-dark btn-sm" data-current-card="#createObjectCard" data-target-card="#objectsCard">
                <i class="fas fa-times"></i>
            </div>
        </div>
    </div>
    <hr/>

    <div class="alert alert-warning">
        <i class="fas fa-exclamation-triangle me-2"></i>
        @lang('fuel_transactions.manual_entry_warning')
    </div>

    <div>
        <div class="my-2 row">
            <label for="vehicle_id" class="col-sm-3 col-form-label">
                @lang('fuel_transactions.Vehicle') <span class="text-danger float-end">*</span>
            </label>
            <div class="col-sm-9">
                <select id="vehicle_id" class="form-control"></select>
                <div style="padding: 5px 7px; display: none" id="vehicle_idErr" class="err-msg mt-2 alert alert-danger"></div>
            </div>
        </div>

        <div class="my-2 row">
            <label for="station_id" class="col-sm-3 col-form-label">
                @lang('fuel_transactions.Station') <span class="text-danger float-end">*</span>
            </label>
            <div class="col-sm-9">
                <select id="station_id" class="form-control"></select>
                <div style="padding: 5px 7px; display: none" id="station_idErr" class="err-msg mt-2 alert alert-danger"></div>
            </div>
        </div>

        <div class="my-2 row">
            <label for="fuel_type_id" class="col-sm-3 col-form-label">
                @lang('fuel_transactions.Fuel Type') <span class="text-danger float-end">*</span>
            </label>
            <div class="col-sm-9">
                <select id="fuel_type_id" class="form-control"></select>
                <div style="padding: 5px 7px; display: none" id="fuel_type_idErr" class="err-msg mt-2 alert alert-danger"></div>
            </div>
        </div>

        <div class="my-2 row">
            <label for="total_amount" class="col-sm-3 col-form-label">
                @lang('fuel_transactions.Amount') (EGP) <span class="text-danger float-end">*</span>
            </label>
            <div class="col-sm-9">
                <input type="number" step="0.01" min="0.01" class="form-control" id="total_amount" placeholder="@lang('fuel_transactions.Amount')">
                <div style="padding: 5px 7px; display: none" id="total_amountErr" class="err-msg mt-2 alert alert-danger"></div>
            </div>
        </div>

        <div class="my-2 row">
            <label for="meter_image" class="col-sm-3 col-form-label">
                @lang('fuel_transactions.Meter Image')
            </label>
            <div class="col-sm-9">
                <input type="file" class="form-control" id="meter_image" accept="image/jpeg,image/jpg,image/png">
                <small class="text-muted">@lang('fuel_transactions.image_optional_admin')</small>
                <div style="padding: 5px 7px; display: none" id="meter_imageErr" class="err-msg mt-2 alert alert-danger"></div>
            </div>
        </div>

        <button class="create-object btn btn-primary float-end">
            <i class="fas fa-save me-1"></i>
            @lang('fuel_transactions.Create Transaction')
        </button>
    </div>
</div>
