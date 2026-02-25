<div class="modal fade" id="documentUploadModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">@lang('clients.Add New File')</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="documentUploadForm" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" id="uploadClientId" name="client_id">
                    <div class="mb-3">
                        <label for="docTitle" class="form-label">@lang('clients.Name')</label>
                        <input type="text" class="form-control" id="docTitle" name="title" placeholder="Optional title">
                    </div>
                    <div class="mb-3">
                        <label for="docFile" class="form-label">File <span class="text-danger">*</span></label>
                        <input type="file" class="form-control" id="docFile" name="file" accept=".pdf,.jpg,.jpeg,.png" required>
                        <small class="text-muted">PDF, JPG, PNG only. Max 10MB.</small>
                    </div>
                    <div id="documentUploadProgress" class="progress mt-2" style="display: none;">
                        <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 0%">0%</div>
                    </div>
                    <div id="documentUploadError" class="alert alert-danger mt-2" style="display: none;"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="documentUploadSubmitBtn">
                        <span class="btn-text">Upload</span>
                        <span class="spinner-border spinner-border-sm ms-2" role="status" style="display: none;"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
