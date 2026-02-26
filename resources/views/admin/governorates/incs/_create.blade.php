<div style="display:none" id="createObjectCard" class="card card-body">
    <div class="row">
        <div class="col-6"><h5>@lang('governorates.Create')</h5></div>
        <div class="col-6 text-end">
            <div class="toggle-btn btn btn-outline-dark btn-sm" data-current-card="#createObjectCard" data-target-card="#objectsCard">
                <i class="fas fa-times"></i>
            </div>
        </div>
    </div>
    <hr/>
    <form id="createGovernorateForm">
        <div class="mb-3">
            <label class="form-label">@lang('governorates.Name') <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="name" name="name" placeholder="@lang('governorates.Name')">
            <div id="nameErr" class="err-msg mt-1 text-danger small" style="display:none;"></div>
        </div>
        <button type="button" class="btn btn-primary create-object">@lang('layouts.save')</button>
    </form>
</div>
