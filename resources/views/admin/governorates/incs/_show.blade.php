<div style="display:none" id="showObjectsCard" class="card card-body">
    <div class="row">
        <div class="col-6"><h5>@lang('layouts.details')</h5></div>
        <div class="col-6 text-end">
            <div class="toggle-btn btn btn-outline-dark btn-sm" data-current-card="#showObjectsCard" data-target-card="#objectsCard">
                <i class="fas fa-times"></i>
            </div>
        </div>
    </div>
    <hr/>
    <div class="mb-3">
        <label class="form-label">@lang('governorates.Name')</label>
        <input type="text" disabled class="form-control" id="show-name">
    </div>
    <div class="mb-3">
        <label class="form-label">@lang('governorates.Districts')</label>
        <div style="height:200px;overflow-y:scroll">
            <table class="table table-sm"><tbody id="show-districtsTable"></tbody></table>
        </div>
    </div>
</div>
