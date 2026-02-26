<div style="display: none" id="editObjectsCard" class="card card-body">
    <div class="row">
        <div class="col-6">
            <h5>@lang('governorates.Update')</h5>
        </div>
        <div class="col-6 text-end">
            <div class="toggle-btn btn btn-outline-dark btn-sm" data-current-card="#editObjectsCard" data-target-card="#objectsCard">
                <i class="fas fa-times"></i>
            </div>
        </div>
    </div>
    <hr/>
    <form id="editGovernorateForm">
        <input type="hidden" id="edit-id" name="id">
        <div class="mb-3">
            <label class="form-label">@lang('governorates.Name') <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="edit-name" name="name" placeholder="@lang('governorates.Name')">
            <div id="edit-nameErr" class="err-msg mt-1 text-danger small" style="display: none;"></div>
        </div>
        <button type="button" class="btn btn-warning update-object">@lang('layouts.Update')</button>
    </form>
</div>
