<!-- Vehicle Detail Modal -->
<div class="modal fade" id="vehicleDetailModal" tabindex="-1" aria-labelledby="vehicleDetailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="vehicleDetailModalLabel">
                    <i class="fas fa-car me-2"></i>@lang('reports.Vehicle Details')
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Loading -->
                <div id="vehicle-detail-loading" class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>

                <!-- Content -->
                <div id="vehicle-detail-content" style="display: none;">
                    <div class="alert alert-info mb-3" id="vehicle-detail-stats"></div>

                    <div class="table-responsive">
                        <table class="table table-bordered table-hover text-center" id="vehicle-detail-table">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>@lang('reports.Date')</th>
                                    <th>@lang('reports.Station')</th>
                                    <th>@lang('reports.Fuel Type')</th>
                                    <th>@lang('reports.Liters')</th>
                                    <th>@lang('reports.Amount')</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">@lang('layouts.close')</button>
            </div>
        </div>
    </div>
</div>
