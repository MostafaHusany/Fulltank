<div style="display: none" id="editObjectsCard" class="card card-body">
    <div class="row">
        <div class="col-6"><h5>@lang('stations.Title') - @lang('layouts.edit')</h5></div>
        <div class="col-6 text-end">
            <div class="toggle-btn btn btn-outline-dark btn-sm" data-current-card="#editObjectsCard" data-target-card="#objectsCard"><i class="fas fa-times"></i></div>
        </div>
    </div>
    <hr/>
    <div>
        <input type="hidden" id="edit-id">
        <div class="my-2 row">
            <label for="edit-name" class="col-sm-3 col-form-label">@lang('stations.Name') <span class="text-danger float-end">*</span></label>
            <div class="col-sm-9"><input type="text" class="form-control" id="edit-name"><div style="padding:5px 7px;display:none" id="edit-nameErr" class="err-msg mt-2 alert alert-danger"></div></div>
        </div>
        <div class="my-2 row">
            <label for="edit-governorate_id" class="col-sm-3 col-form-label">@lang('stations.Governorate') <span class="text-danger float-end">*</span></label>
            <div class="col-sm-9"><select class="form-control" id="edit-governorate_id"></select></div>
        </div>
        <div class="my-2 row">
            <label for="edit-district_id" class="col-sm-3 col-form-label">@lang('stations.District') <span class="text-danger float-end">*</span></label>
            <div class="col-sm-9"><select class="form-control" id="edit-district_id"></select></div>
        </div>
        <div class="my-2 row">
            <label for="edit-address" class="col-sm-3 col-form-label">@lang('stations.Address')</label>
            <div class="col-sm-9"><input type="text" class="form-control" id="edit-address"></div>
        </div>
        <div class="my-2 row">
            <label class="col-sm-3 col-form-label">@lang('stations.Location')</label>
            <div class="col-sm-9"><div id="stationMapEdit" style="width:100%;min-height:250px;height:250px;"></div><input type="hidden" id="edit-lat"><input type="hidden" id="edit-lng"></div>
        </div>
        <div class="my-2 row">
            <label for="edit-nearby_landmarks" class="col-sm-3 col-form-label">@lang('stations.Nearby Landmarks')</label>
            <div class="col-sm-9"><textarea class="form-control" id="edit-nearby_landmarks" rows="2"></textarea></div>
        </div>
        <div class="my-2 row">
            <label for="edit-manager_name" class="col-sm-3 col-form-label">@lang('stations.Manager Name') <span class="text-danger float-end">*</span></label>
            <div class="col-sm-9"><input type="text" class="form-control" id="edit-manager_name"></div>
        </div>
        <div class="my-2 row">
            <label for="edit-phone_1" class="col-sm-3 col-form-label">@lang('stations.Phone 1') <span class="text-danger float-end">*</span></label>
            <div class="col-sm-9"><input type="text" class="form-control" id="edit-phone_1"></div>
        </div>
        <div class="my-2 row">
            <label for="edit-phone_2" class="col-sm-3 col-form-label">@lang('stations.Phone 2')</label>
            <div class="col-sm-9"><input type="text" class="form-control" id="edit-phone_2"></div>
        </div>
        <div class="my-2 row">
            <label for="edit-email" class="col-sm-3 col-form-label">@lang('stations.Email')</label>
            <div class="col-sm-9"><input type="email" class="form-control" id="edit-email" placeholder="@lang('stations.Email')"></div>
        </div>
        <div class="my-2 row">
            <label for="edit-password" class="col-sm-3 col-form-label">@lang('stations.Password')</label>
            <div class="col-sm-9"><input type="password" class="form-control" id="edit-password" placeholder="@lang('stations.Password')"></div>
        </div>
        <div class="my-2 row">
            <label for="edit-fuel_type_ids" class="col-sm-3 col-form-label">@lang('stations.Fuel Types') <span class="text-danger float-end">*</span></label>
            <div class="col-sm-9"><select class="form-control" id="edit-fuel_type_ids" multiple="multiple"></select></div>
        </div>
        <div class="my-2 row">
            <label for="edit-bank_account_details" class="col-sm-3 col-form-label">@lang('stations.Bank Account') <span class="text-danger float-end">*</span></label>
            <div class="col-sm-9"><textarea class="form-control" id="edit-bank_account_details" rows="2"></textarea></div>
        </div>
        <button class="update-object btn btn-warning float-end">@lang('layouts.Update')</button>
    </div>
</div>
