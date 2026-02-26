<div style="display: none" id="createObjectCard" class="card card-body">
    <div class="row">
        <div class="col-6">
            <h5>@lang('fuel_types.Add')</h5>
        </div>
        <div class="col-6 text-end">
            <div class="toggle-btn btn btn-outline-dark btn-sm" data-current-card="#createObjectCard" data-target-card="#objectsCard">
                <i class="fas fa-times"></i>
            </div>
        </div>
    </div><!-- /.row -->
    <hr/>
    <div>
        <div class="my-2 row">
            <label for="name" class="col-sm-3 col-form-label">@lang('fuel_types.Name') <span class="text-danger float-end">*</span></label>
            <div class="col-sm-9">
                <input type="text" class="form-control" id="name" placeholder="@lang('fuel_types.Name')">
                <div style="padding: 5px 7px; display: none" id="nameErr" class="err-msg mt-2 alert alert-danger"></div>
            </div><!-- /.col-sm-9 -->
        </div><!-- /.my-2 -->
        <div class="my-2 row">
            <label for="price_per_liter" class="col-sm-3 col-form-label">@lang('fuel_types.Price') <span class="text-danger float-end">*</span></label>
            <div class="col-sm-9">
                <input type="number" step="0.01" min="0" class="form-control" id="price_per_liter" placeholder="@lang('fuel_types.Price')">
                <div style="padding: 5px 7px; display: none" id="price_per_literErr" class="err-msg mt-2 alert alert-danger"></div>
            </div><!-- /.col-sm-9 -->
        </div><!-- /.my-2 -->
        <div class="my-2 row">
            <label for="description" class="col-sm-3 col-form-label">@lang('fuel_types.Description')</label>
            <div class="col-sm-9">
                <input type="text" class="form-control" id="description" placeholder="@lang('fuel_types.Description')">
                <div style="padding: 5px 7px; display: none" id="descriptionErr" class="err-msg mt-2 alert alert-danger"></div>
            </div><!-- /.col-sm-9 -->
        </div><!-- /.my-2 -->
        <button class="create-object btn btn-primary float-end">@lang('layouts.save')</button>
    </div>
</div>
