<div class="modal fade" id="stationDetailsModal" tabindex="-1" aria-labelledby="stationDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="stationDetailsModalLabel">
                    <i class="fas fa-gas-pump me-2"></i>
                    @lang('settlements.Station Details')
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <table class="table table-borderless mb-0">
                    <tbody id="stationDetailsBody">
                    </tbody>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">@lang('layouts.Close')</button>
            </div>
        </div>
    </div>
</div>
