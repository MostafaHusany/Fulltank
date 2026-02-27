<div style="display: none" id="createObjectCard" class="card card-body">
    <div class="row">
        <div class="col-6">
            <h5>@lang('station_workers.Create_Title')</h5>
        </div>
        <div class="col-6 text-end">
            <div class="toggle-btn btn btn-outline-dark btn-sm" data-current-card="#createObjectCard" data-target-card="#objectsCard">
                <i class="fas fa-times"></i>
            </div>
        </div>
    </div>
    <hr/>

    <div>
        <div class="my-2 row">
            <label for="full_name" class="col-sm-3 col-form-label">
                @lang('station_workers.Full Name') <span class="text-danger float-end">*</span>
            </label>
            <div class="col-sm-9">
                <input type="text" class="form-control" id="full_name" placeholder="@lang('station_workers.Full Name')">
                <div style="padding: 5px 7px; display: none" id="full_nameErr" class="err-msg mt-2 alert alert-danger"></div>
            </div>
        </div>

        <div class="my-2 row">
            <label for="station_id" class="col-sm-3 col-form-label">
                @lang('station_workers.Station') <span class="text-danger float-end">*</span>
            </label>
            <div class="col-sm-9">
                <select id="station_id" class="form-control"></select>
                <div style="padding: 5px 7px; display: none" id="station_idErr" class="err-msg mt-2 alert alert-danger"></div>
            </div>
        </div>

        <div class="my-2 row">
            <label for="phone" class="col-sm-3 col-form-label">
                @lang('station_workers.Phone')
            </label>
            <div class="col-sm-9">
                <input type="text" class="form-control" id="phone" placeholder="@lang('station_workers.Phone')">
                <div style="padding: 5px 7px; display: none" id="phoneErr" class="err-msg mt-2 alert alert-danger"></div>
            </div>
        </div>

        <hr/>

        <div class="my-2 row">
            <label for="username" class="col-sm-3 col-form-label">
                @lang('station_workers.Username') <span class="text-danger float-end">*</span>
            </label>
            <div class="col-sm-9">
                <input type="text" class="form-control" id="username" placeholder="@lang('station_workers.Username')" autocomplete="off">
                <div style="padding: 5px 7px; display: none" id="usernameErr" class="err-msg mt-2 alert alert-danger"></div>
            </div>
        </div>

        <div class="my-2 row">
            <label for="password" class="col-sm-3 col-form-label">
                @lang('station_workers.Password') <span class="text-danger float-end">*</span>
            </label>
            <div class="col-sm-9">
                <input type="password" class="form-control" id="password" placeholder="@lang('station_workers.Password')" autocomplete="new-password">
                <div style="padding: 5px 7px; display: none" id="passwordErr" class="err-msg mt-2 alert alert-danger"></div>
            </div>
        </div>

        <button class="create-object btn btn-primary float-end">@lang('station_workers.Create_Title')</button>
    </div>
</div>
