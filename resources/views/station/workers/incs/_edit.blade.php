<div class="modal fade" id="editWorkerModal" tabindex="-1" aria-labelledby="editWorkerModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-station text-white">
                <h5 class="modal-title" id="editWorkerModalLabel">
                    <i class="fas fa-user-edit me-2"></i>@lang('station.workers.edit_worker')
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editWorkerForm">
                <input type="hidden" id="editWorkerId" name="id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="editWorkerName" class="form-label">@lang('station.workers.name') <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="editWorkerName" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="editWorkerPhone" class="form-label">@lang('station.workers.phone')</label>
                        <input type="text" class="form-control" id="editWorkerPhone" name="phone">
                    </div>
                    <div class="mb-3">
                        <label for="editWorkerUsername" class="form-label">@lang('station.workers.username') <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="editWorkerUsername" name="username" required>
                    </div>
                    <div class="mb-3">
                        <label for="editWorkerPassword" class="form-label">@lang('station.workers.password')</label>
                        <input type="password" class="form-control" id="editWorkerPassword" name="password" minlength="6">
                        <small class="text-muted">@lang('station.workers.password_edit_hint')</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">@lang('layouts.close')</button>
                    <button type="submit" class="btn btn-station">
                        <i class="fas fa-save me-1"></i>@lang('station.workers.update')
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
