<div style="display: none" id="editObjectsCard" class="card card-body">
    <div class="row">
        <div class="col-6">
            <h5>@lang('station_workers.Update_Title')</h5>
        </div>
        <div class="col-6 text-end">
            <div class="toggle-btn btn btn-outline-dark btn-sm" data-current-card="#editObjectsCard" data-target-card="#objectsCard">
                <i class="fas fa-times"></i>
            </div>
        </div>
    </div>
    <hr/>

    <div>
        <input type="hidden" id="edit-id">

        <div class="my-2 row">
            <label for="edit-full_name" class="col-sm-3 col-form-label">
                @lang('station_workers.Full Name') <span class="text-danger float-end">*</span>
            </label>
            <div class="col-sm-9">
                <input type="text" class="form-control" id="edit-full_name" placeholder="@lang('station_workers.Full Name')">
                <div style="padding: 5px 7px; display: none" id="edit-full_nameErr" class="err-msg mt-2 alert alert-danger"></div>
            </div>
        </div>

        <div class="my-2 row">
            <label for="edit-station_id" class="col-sm-3 col-form-label">
                @lang('station_workers.Station') <span class="text-danger float-end">*</span>
            </label>
            <div class="col-sm-9">
                <select id="edit-station_id" class="form-control"></select>
                <div style="padding: 5px 7px; display: none" id="edit-station_idErr" class="err-msg mt-2 alert alert-danger"></div>
            </div>
        </div>

        <div class="my-2 row">
            <label for="edit-phone" class="col-sm-3 col-form-label">
                @lang('station_workers.Phone')
            </label>
            <div class="col-sm-9">
                <input type="text" class="form-control" id="edit-phone" placeholder="@lang('station_workers.Phone')">
                <div style="padding: 5px 7px; display: none" id="edit-phoneErr" class="err-msg mt-2 alert alert-danger"></div>
            </div>
        </div>

        <hr/>

        <div class="my-2 row">
            <label for="edit-username" class="col-sm-3 col-form-label">
                @lang('station_workers.Username') <span class="text-danger float-end">*</span>
            </label>
            <div class="col-sm-9">
                <input type="text" class="form-control" id="edit-username" placeholder="@lang('station_workers.Username')" autocomplete="off">
                <div style="padding: 5px 7px; display: none" id="edit-usernameErr" class="err-msg mt-2 alert alert-danger"></div>
            </div>
        </div>

        <div class="my-2 row">
            <label for="edit-password" class="col-sm-3 col-form-label">
                @lang('station_workers.Password')
            </label>
            <div class="col-sm-9">
                <input type="password" class="form-control" id="edit-password" placeholder="@lang('station_workers.Leave blank to keep current')" autocomplete="new-password">
                <div style="padding: 5px 7px; display: none" id="edit-passwordErr" class="err-msg mt-2 alert alert-danger"></div>
            </div>
        </div>

        <button class="update-object btn btn-warning float-end">@lang('station_workers.Update_Title')</button>
    </div>
</div>
