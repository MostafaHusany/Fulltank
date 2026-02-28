<div class="modal fade" id="addWorkerModal" tabindex="-1" aria-labelledby="addWorkerModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-station text-white">
                <h5 class="modal-title" id="addWorkerModalLabel">
                    <i class="fas fa-user-plus me-2"></i>@lang('station.workers.add_worker')
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="addWorkerForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="workerName" class="form-label">@lang('station.workers.name') <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="workerName" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="workerPhone" class="form-label">@lang('station.workers.phone')</label>
                        <input type="text" class="form-control" id="workerPhone" name="phone" placeholder="05XXXXXXXX">
                    </div>
                    <div class="mb-3">
                        <label for="workerUsername" class="form-label">@lang('station.workers.username') <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="workerUsername" name="username" required>
                        <small class="text-muted">@lang('station.workers.username_hint')</small>
                    </div>
                    <div class="mb-3">
                        <label for="workerPassword" class="form-label">@lang('station.workers.password') <span class="text-danger">*</span></label>
                        <input type="password" class="form-control" id="workerPassword" name="password" required minlength="6">
                        <small class="text-muted">@lang('station.workers.password_hint')</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">@lang('layouts.close')</button>
                    <button type="submit" class="btn btn-station">
                        <i class="fas fa-save me-1"></i>@lang('station.workers.save')
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
