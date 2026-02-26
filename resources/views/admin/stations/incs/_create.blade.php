<div style="display: none" id="createObjectCard" class="card card-body">
    <div class="row">
        <div class="col-6"><h5>@lang('stations.Title') - @lang('layouts.add')</h5></div>
        <div class="col-6 text-end">
            <div class="toggle-btn btn btn-outline-dark btn-sm" data-current-card="#createObjectCard" data-target-card="#objectsCard"><i class="fas fa-times"></i></div>
        </div>
    </div>
    <hr/>
    <div>
        <div class="my-2 row">
            <label for="name" class="col-sm-3 col-form-label">@lang('stations.Name') <span class="text-danger float-end">*</span></label>
            <div class="col-sm-9"><input type="text" class="form-control" id="name"><div style="padding:5px 7px;display:none" id="nameErr" class="err-msg mt-2 alert alert-danger"></div></div>
        </div>
        <div class="my-2 row">
            <label for="governorate_id" class="col-sm-3 col-form-label">@lang('stations.Governorate') <span class="text-danger float-end">*</span></label>
            <div class="col-sm-9"><select class="form-control" id="governorate_id"></select><div style="padding:5px 7px;display:none" id="governorate_idErr" class="err-msg mt-2 alert alert-danger"></div></div>
        </div>
        <div class="my-2 row">
            <label for="district_id" class="col-sm-3 col-form-label">@lang('stations.District') <span class="text-danger float-end">*</span></label>
            <div class="col-sm-9"><select class="form-control" id="district_id"></select><div style="padding:5px 7px;display:none" id="district_idErr" class="err-msg mt-2 alert alert-danger"></div></div>
        </div>
        <div class="my-2 row">
            <label for="address" class="col-sm-3 col-form-label">@lang('stations.Address')</label>
            <div class="col-sm-9"><input type="text" class="form-control" id="address"></div>
        </div>
        <div class="my-2 row">
            <label class="col-sm-3 col-form-label">@lang('stations.Location')</label>
            <div class="col-sm-9"><div id="stationMapCreate" style="width:100%;min-height:250px;height:250px;"></div><input type="hidden" id="lat"><input type="hidden" id="lng"></div>
        </div>
        <div class="my-2 row">
            <label for="nearby_landmarks" class="col-sm-3 col-form-label">@lang('stations.Nearby Landmarks')</label>
            <div class="col-sm-9"><textarea class="form-control" id="nearby_landmarks" rows="2"></textarea></div>
        </div>
        <div class="my-2 row">
            <label for="manager_name" class="col-sm-3 col-form-label">@lang('stations.Manager Name') <span class="text-danger float-end">*</span></label>
            <div class="col-sm-9"><input type="text" class="form-control" id="manager_name"></div>
        </div>
        <div class="my-2 row">
            <label for="phone_1" class="col-sm-3 col-form-label">@lang('stations.Phone 1') <span class="text-danger float-end">*</span></label>
            <div class="col-sm-9"><input type="text" class="form-control" id="phone_1"><div style="padding:5px 7px;display:none" id="phone_1Err" class="err-msg mt-2 alert alert-danger"></div></div>
        </div>
        <div class="my-2 row">
            <label for="phone_2" class="col-sm-3 col-form-label">@lang('stations.Phone 2')</label>
            <div class="col-sm-9"><input type="text" class="form-control" id="phone_2"></div>
        </div>
        <div class="my-2 row">
            <label for="email" class="col-sm-3 col-form-label">@lang('stations.Email') <span class="text-danger float-end">*</span></label>
            <div class="col-sm-9"><input type="email" class="form-control" id="email"><div style="padding:5px 7px;display:none" id="emailErr" class="err-msg mt-2 alert alert-danger"></div></div>
        </div>
        <div class="my-2 row">
            <label for="password" class="col-sm-3 col-form-label">@lang('stations.Password') <span class="text-danger float-end">*</span></label>
            <div class="col-sm-9"><input type="password" class="form-control" id="password"><div style="padding:5px 7px;display:none" id="passwordErr" class="err-msg mt-2 alert alert-danger"></div></div>
        </div>
        <div class="my-2 row">
            <label for="fuel_type_ids" class="col-sm-3 col-form-label">@lang('stations.Fuel Types') <span class="text-danger float-end">*</span></label>
            <div class="col-sm-9"><select class="form-control" id="fuel_type_ids" multiple="multiple"></select><div style="padding:5px 7px;display:none" id="fuel_type_idsErr" class="err-msg mt-2 alert alert-danger"></div></div>
        </div>
        <div class="my-2 row">
            <label for="bank_account_details" class="col-sm-3 col-form-label">@lang('stations.Bank Account') <span class="text-danger float-end">*</span></label>
            <div class="col-sm-9"><textarea class="form-control" id="bank_account_details" rows="2"></textarea></div>
        </div>
        <button class="create-object btn btn-primary float-end">@lang('layouts.save')</button>
    </div>
</div>
