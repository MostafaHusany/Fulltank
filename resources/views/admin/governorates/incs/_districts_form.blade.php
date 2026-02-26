<div style="display:none" id="manageDistricts" class="card card-body">
        <div class="row">
            <div class="col-6"><h5>@lang('governorates.Manage Districts')</h5></div>
            <div class="col-6 text-end">
                <div class="toggle-btn btn btn-outline-dark btn-sm" data-current-card="#manageDistricts" data-target-card="#objectsCard"><i class="fas fa-times"></i></div>
            </div>
        </div>

    <hr/>

    <div class="mb-3">
        <label class="form-label">@lang('governorates.Add District')</label>
        <div class="row g-2">
            <div class="col-8"><input type="text" class="form-control" id="district-name" placeholder="@lang('governorates.District Name')"></div>
            <div class="col-4"><button id="add-district" class="btn btn-sm btn-primary"><i class="fas fa-plus-circle"></i></button></div>
        </div>
    </div>

    <div class="mb-3">
        <label class="form-label">@lang('governorates.Districts')</label>
        <div style="height:250px;overflow-y:scroll">
            <table class="table table-sm text-center">
                <thead><tr><th>@lang('governorates.District Name')</th><th>@lang('layouts.Actions')</th></tr></thead>
                <tbody id="districtsTable"></tbody>
            </table>
        </div>
    </div>
</div>
