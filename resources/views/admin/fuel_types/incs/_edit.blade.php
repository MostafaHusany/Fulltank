<div style="display: none" id="editObjectsCard" class="card card-body">
    <div class="row">
        <div class="col-6">
            <h5>@lang('fuel_types.Edit')</h5>
        </div>
        <div class="col-6 text-end">
            <div class="toggle-btn btn btn-outline-dark btn-sm" data-current-card="#editObjectsCard" data-target-card="#objectsCard">
                <i class="fas fa-times"></i>
            </div>
        </div>
    </div><!-- /.row -->
    <hr/>
    <div>
        <input type="hidden" id="edit-id">
        <div class="my-2 row">
            <label for="edit-name" class="col-sm-3 col-form-label">@lang('fuel_types.Name') <span class="text-danger float-end">*</span></label>
            <div class="col-sm-9">
                <input type="text" class="form-control" id="edit-name" placeholder="@lang('fuel_types.Name')">
                <div style="padding: 5px 7px; display: none" id="edit-nameErr" class="err-msg mt-2 alert alert-danger"></div>
            </div><!-- /.col-sm-9 -->
        </div><!-- /.my-2 -->
        <div class="my-2 row">
            <label for="edit-price_per_liter" class="col-sm-3 col-form-label">@lang('fuel_types.Price') <span class="text-danger float-end">*</span></label>
            <div class="col-sm-9">
                <input type="number" step="0.01" min="0" class="form-control" id="edit-price_per_liter" placeholder="@lang('fuel_types.Price')">
                <div style="padding: 5px 7px; display: none" id="edit-price_per_literErr" class="err-msg mt-2 alert alert-danger"></div>
            </div><!-- /.col-sm-9 -->
        </div><!-- /.my-2 -->
        <div class="my-2 row">
            <label for="edit-description" class="col-sm-3 col-form-label">@lang('fuel_types.Description')</label>
            <div class="col-sm-9">
                <input type="text" class="form-control" id="edit-description" placeholder="@lang('fuel_types.Description')">
                <div style="padding: 5px 7px; display: none" id="edit-descriptionErr" class="err-msg mt-2 alert alert-danger"></div>
            </div><!-- /.col-sm-9 -->
        </div><!-- /.my-2 -->
        <button class="update-object btn btn-warning float-end">@lang('layouts.Update')</button>
    </div>
</div>
