
<div style="display: none" id="showObjectsCard" class="card card-body">
    <div class="row g-2 align-items-center">
        <div class="col-8 col-md-6">
            <h5 class="mb-0">@lang('client.vehicles.show_title')</h5>
        </div>
        <div class="col-4 col-md-6 text-end">
            <button type="button" class="toggle-btn btn btn-outline-dark btn-sm" data-current-card="#showObjectsCard" data-target-card="#objectsCard">
                <i class="fas fa-times"></i>
            </button>
        </div>
    </div>
    <hr class="my-3">

    <ul class="nav nav-tabs mb-3" id="vehicleShowTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="vehicle-tab-details-btn" data-bs-toggle="tab" data-bs-target="#vehicle-tab-details" type="button" role="tab" aria-controls="vehicle-tab-details" aria-selected="true">
                @lang('vehicles.Tab details')
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="vehicle-tab-routes-btn" data-bs-toggle="tab" data-bs-target="#vehicle-tab-routes" type="button" role="tab" aria-controls="vehicle-tab-routes" aria-selected="false">
                @lang('vehicles.Tab daily routes')
            </button>
        </li>
    </ul>

    <div class="tab-content" id="vehicleShowTabContent">
        <div class="tab-pane fade show active" id="vehicle-tab-details" role="tabpanel" aria-labelledby="vehicle-tab-details-btn">
            <div class="row g-3">
                <div class="col-12 col-md-6">
                    <label class="text-muted small">@lang('client.vehicles.plate_number')</label>
                    <div class="fw-semibold" id="show-plate_number">---</div>
                </div>
                <div class="col-12 col-md-6">
                    <label class="text-muted small">@lang('client.vehicles.model')</label>
                    <div class="fw-semibold" id="show-model">---</div>
                </div>
                <div class="col-12 col-md-6">
                    <label class="text-muted small">@lang('client.vehicles.fuel_type')</label>
                    <div class="fw-semibold" id="show-fuel_type">---</div>
                </div>
                <div class="col-12 col-md-6">
                    <label class="text-muted small">@lang('client.vehicles.status')</label>
                    <div id="show-status">---</div>
                </div>
                <div class="col-12 col-md-6">
                    <label class="text-muted small">@lang('client.vehicles.quota')</label>
                    <div class="fw-semibold" id="show-quota">---</div>
                </div>
            </div>

            <hr class="my-3">
            <h6 class="fw-semibold mb-2">@lang('vehicles.Fuel trip history')</h6>
            <div class="table-responsive border rounded">
                <table class="table table-sm table-striped mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>@lang('vehicles.Trip date')</th>
                            <th>@lang('vehicles.Station')</th>
                            <th>@lang('vehicles.Driver')</th>
                            <th>@lang('vehicles.Liters')</th>
                            <th>@lang('vehicles.Amount')</th>
                        </tr>
                    </thead>
                    <tbody id="vehicle-show-trips-body">
                        <tr><td colspan="5" class="text-muted text-center py-3">@lang('vehicles.No fuel visits')</td></tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="tab-pane fade" id="vehicle-tab-routes" role="tabpanel" aria-labelledby="vehicle-tab-routes-btn">
            <p class="text-muted small mb-2">@lang('vehicles.Daily routes hint')</p>
            <div class="table-responsive border rounded mb-3">
                <table class="table table-sm table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>@lang('vehicles.Route date')</th>
                            <th>@lang('vehicles.Points')</th>
                            <th>@lang('vehicles.Distance km')</th>
                            <th>@lang('vehicles.Window')</th>
                        </tr>
                    </thead>
                    <tbody id="vehicle-daily-routes-body">
                        <tr><td colspan="4" class="text-muted text-center py-3">@lang('vehicles.No daily routes')</td></tr>
                    </tbody>
                </table>
            </div>
            <h6 class="fw-semibold mb-2">@lang('vehicles.Selected day map')</h6>
            <div id="vehicle-daily-route-map" class="vehicle-admin-map border rounded"></div>
        </div>
    </div>
</div>
