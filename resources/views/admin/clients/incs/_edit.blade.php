<div style="display: none" id="editObjectsCard" class="card card-body">
    <div class="row">
        <div class="col-6">
            <h5>@lang('clients.Update Client')</h5>
        </div>
        <div class="col-6 text-end">
            <div class="toggle-btn btn btn-outline-dark btn-sm" data-current-card="#editObjectsCard" data-target-card="#objectsCard">
                <i class="fas fa-times"></i>
            </div>
        </div>
    </div>
    <hr/>
    <form action="/" id="objectForm" enctype="multipart/form-data">
        <input type="hidden" id="edit-id">
        <div class="my-2 row">
            <label for="edit-name" class="col-sm-2 col-form-label">@lang('clients.Name') <span class="text-danger">*</span></label>
            <div class="col-sm-10">
                <input type="text" class="form-control" id="edit-name">
                <div style="display: none" id="edit-nameErr" class="err-msg mt-2 alert alert-danger"></div>
            </div>
        </div>
        <div class="my-2 row">
            <label for="edit-company_name" class="col-sm-2 col-form-label">@lang('clients.Company Name') <span class="text-danger">*</span></label>
            <div class="col-sm-10">
                <input type="text" class="form-control" id="edit-company_name">
                <div style="display: none" id="edit-company_nameErr" class="err-msg mt-2 alert alert-danger"></div>
            </div>
        </div>
        <div class="my-2 row">
            <label for="edit-client_category" class="col-sm-2 col-form-label">@lang('clients.Client Type') <span class="text-danger">*</span></label>
            <div class="col-sm-10">
                <select class="form-control" id="edit-client_category" style="width: 100%;"></select>
                <div style="display: none" id="edit-client_categoryErr" class="err-msg mt-2 alert alert-danger"></div>
            </div>
        </div>
        <div class="my-2 row">
            <label for="edit-email" class="col-sm-2 col-form-label">@lang('clients.Email') <span class="text-danger">*</span></label>
            <div class="col-sm-10">
                <input type="email" class="form-control" id="edit-email">
                <div style="display: none" id="edit-emailErr" class="err-msg mt-2 alert alert-danger"></div>
            </div>
        </div>
        <div class="my-2 row">
            <label for="edit-phone" class="col-sm-2 col-form-label">@lang('clients.Phone') <span class="text-danger">*</span></label>
            <div class="col-sm-10">
                <input type="text" class="form-control" id="edit-phone">
                <div style="display: none" id="edit-phoneErr" class="err-msg mt-2 alert alert-danger"></div>
            </div>
        </div>
        <div class="my-2 row">
            <label for="edit-password" class="col-sm-2 col-form-label">@lang('clients.Password')</label>
            <div class="col-sm-10">
                <input type="password" class="form-control" id="edit-password">
            </div>
        </div>
        <div class="my-2 row">
            <label for="edit-picture" class="col-sm-2 col-form-label">@lang('clients.Picture')</label>
            <div class="col-sm-10">
                <input type="file" class="form-control" id="edit-picture" accept="image/*">
            </div>
        </div>
        <button type="button" class="update-object btn btn-warning float-end">@lang('clients.Update Client')</button>
    </form>
</div>
