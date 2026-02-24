<div style="display: none" id="createObjectCard" class="card card-body">
    <div class="row">
        <div class="col-6"><h5>{{ __('clients.Create Client') }}</h5></div>
        <div class="col-6 text-end">
            <div class="toggle-btn btn btn-outline-dark btn-sm" data-current-card="#createObjectCard" data-target-card="#objectsCard"><i class="fas fa-times"></i></div>
        </div>
    </div>
    <hr/>
    <form action="/" id="objectForm" enctype="multipart/form-data">
        <div class="my-2 row">
            <label for="name" class="col-sm-2 col-form-label">{{ __('clients.Name') }} <span class="text-danger">*</span></label>
            <div class="col-sm-10">
                <input type="text" class="form-control" id="name">
                <div style="display: none" id="nameErr" class="err-msg mt-2 alert alert-danger"></div>
            </div>
        </div>
        <div class="my-2 row">
            <label for="company_name" class="col-sm-2 col-form-label">{{ __('clients.Company Name') }} <span class="text-danger">*</span></label>
            <div class="col-sm-10">
                <input type="text" class="form-control" id="company_name">
                <div style="display: none" id="company_nameErr" class="err-msg mt-2 alert alert-danger"></div>
            </div>
        </div>
        <div class="my-2 row">
            <label for="client_category" class="col-sm-2 col-form-label">{{ __('clients.Client Type') }} <span class="text-danger">*</span></label>
            <div class="col-sm-10">
                <select class="form-control" id="client_category" style="width: 100%;"></select>
                <div style="display: none" id="client_categoryErr" class="err-msg mt-2 alert alert-danger"></div>
            </div>
        </div>
        <div class="my-2 row">
            <label for="email" class="col-sm-2 col-form-label">{{ __('clients.Email') }} <span class="text-danger">*</span></label>
            <div class="col-sm-10">
                <input type="email" class="form-control" id="email">
                <div style="display: none" id="emailErr" class="err-msg mt-2 alert alert-danger"></div>
            </div>
        </div>
        <div class="my-2 row">
            <label for="phone" class="col-sm-2 col-form-label">{{ __('clients.Phone') }} <span class="text-danger">*</span></label>
            <div class="col-sm-10">
                <input type="text" class="form-control" id="phone">
                <div style="display: none" id="phoneErr" class="err-msg mt-2 alert alert-danger"></div>
            </div>
        </div>
        <div class="my-2 row">
            <label for="password" class="col-sm-2 col-form-label">{{ __('clients.Password') }} <span class="text-danger">*</span></label>
            <div class="col-sm-10">
                <input type="password" class="form-control" id="password">
                <div style="display: none" id="passwordErr" class="err-msg mt-2 alert alert-danger"></div>
            </div>
        </div>
        <div class="my-2 row">
            <label for="picture" class="col-sm-2 col-form-label">{{ __('clients.Picture') }}</label>
            <div class="col-sm-10"><input type="file" class="form-control" id="picture" accept="image/*"></div>
        </div>
        <button type="button" class="create-object btn btn-primary float-end">{{ __('clients.Create Client') }}</button>
    </form>
</div>
