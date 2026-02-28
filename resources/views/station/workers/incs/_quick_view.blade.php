<div class="modal fade" id="quickViewModal" tabindex="-1" aria-labelledby="quickViewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-station text-white">
                <h5 class="modal-title" id="quickViewModalLabel">
                    <i class="fas fa-chart-bar me-2"></i>@lang('station.workers.performance')
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                {{-- Worker Info --}}
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="text-muted small">@lang('station.workers.name')</div>
                        <div class="fw-bold" id="qvWorkerName">-</div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-muted small">@lang('station.workers.username')</div>
                        <div class="fw-bold" id="qvWorkerUsername">-</div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-muted small">@lang('station.workers.phone')</div>
                        <div class="fw-bold" id="qvWorkerPhone">-</div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-muted small">@lang('station.workers.status')</div>
                        <div id="qvWorkerStatus">-</div>
                    </div>
                </div>

                <hr>

                {{-- Today Stats --}}
                <h6 class="text-station mb-3">
                    <i class="fas fa-calendar-day me-2"></i>@lang('station.workers.today_stats')
                </h6>
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="card bg-light">
                            <div class="card-body text-center py-3">
                                <div class="text-muted small">@lang('station.workers.transactions')</div>
                                <div class="fs-4 fw-bold text-station" id="qvTodayTransactions">0</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-light">
                            <div class="card-body text-center py-3">
                                <div class="text-muted small">@lang('station.workers.liters')</div>
                                <div class="fs-4 fw-bold text-info" id="qvTodayLiters">0 L</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-light">
                            <div class="card-body text-center py-3">
                                <div class="text-muted small">@lang('station.workers.amount')</div>
                                <div class="fs-4 fw-bold text-success" id="qvTodayAmount">0</div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Total Stats --}}
                <h6 class="text-muted mb-3">
                    <i class="fas fa-chart-line me-2"></i>@lang('station.workers.total_stats')
                </h6>
                <div class="row">
                    <div class="col-md-4">
                        <div class="card border">
                            <div class="card-body text-center py-3">
                                <div class="text-muted small">@lang('station.workers.transactions')</div>
                                <div class="fs-5 fw-bold" id="qvTotalTransactions">0</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card border">
                            <div class="card-body text-center py-3">
                                <div class="text-muted small">@lang('station.workers.liters')</div>
                                <div class="fs-5 fw-bold" id="qvTotalLiters">0 L</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card border">
                            <div class="card-body text-center py-3">
                                <div class="text-muted small">@lang('station.workers.amount')</div>
                                <div class="fs-5 fw-bold" id="qvTotalAmount">0</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">@lang('layouts.close')</button>
            </div>
        </div>
    </div>
</div>
