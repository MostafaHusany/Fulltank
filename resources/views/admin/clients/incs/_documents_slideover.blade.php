<div class="offcanvas offcanvas-end" tabindex="-1" id="clientDocumentsOffcanvas" aria-labelledby="clientDocumentsOffcanvasLabel" style="width: 480px;">
    <div class="offcanvas-header border-bottom">
        <h5 class="offcanvas-title" id="clientDocumentsOffcanvasLabel">Documents</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body p-0">
        <div class="p-3 border-bottom d-flex justify-content-between align-items-center">
            <span class="text-muted small" id="documentsCountText">0 Documents</span>
            @if($permissions == 'admin' || in_array('clients_add', $permissions) || in_array('clients_edit', $permissions))
            <button type="button" class="btn btn-sm btn-primary" id="clientDocumentsAddBtn"><i class="fas fa-plus"></i> {{ __('clients.Add New File') }}</button>
            @endif
        </div>
        <div class="p-3" id="documentsTableContainer" style="max-height: 70vh; overflow-y: auto;">
            <div id="documentsLoading" class="text-center py-5" style="display: none;"><div class="spinner-border text-primary" role="status"></div></div>
            <table class="table table-sm table-hover mb-0" id="clientDocumentsTable">
                <thead><tr><th>Name</th><th class="text-end">Actions</th></tr></thead>
                <tbody id="clientDocumentsTableBody"></tbody>
            </table>
        </div>
    </div>
</div>

@push('custome-js-2')
<script>
$('document').ready(function () {
    var currentDocumentsClientId = null;
    var docsIndexUrlBase = "{{ route('admin.clients.documents.index', ['clientId' => 'CID']) }}";
    var docsStoreUrlBase = "{{ route('admin.clients.documents.store', ['clientId' => 'CID']) }}";
    var docsViewUrlBase = "{{ route('admin.clients.documents.view', ['documentId' => 'DID']) }}";
    var docsDownloadUrlBase = "{{ route('admin.clients.documents.download', ['documentId' => 'DID']) }}";
    var docsDestroyUrlBase = "{{ route('admin.clients.documents.destroy', ['documentId' => 'DID']) }}";

    function loadClientDocuments(clientId, clientName) {
        currentDocumentsClientId = clientId;
        $('#clientDocumentsOffcanvasLabel').text(clientName + ' - Documents');
        $('#documentsLoading').show();
        $('#clientDocumentsTableBody').empty();
        var url = docsIndexUrlBase.replace('CID', clientId);
        axios.get(url).then(function (res) {
            var data = res.data.data || [];
            $('#documentsCountText').text(data.length + ' Documents');
            data.forEach(function (doc) {
                var viewUrl = docsViewUrlBase.replace('DID', doc.id);
                var downloadUrl = docsDownloadUrlBase.replace('DID', doc.id);
                var row = '<tr><td>' + (doc.title || doc.path) + '</td><td class="text-end">' +
                    '<a href="' + viewUrl + '" target="_blank" class="btn btn-sm btn-outline-info me-1">View</a>' +
                    '<a href="' + downloadUrl + '" class="btn btn-sm btn-outline-secondary me-1">Download</a>' +
                    '<button type="button" class="btn btn-sm btn-outline-danger doc-delete-btn" data-doc-id="' + doc.id + '" data-doc-name="' + (doc.title || 'file') + '">Delete</button>' +
                    '</td></tr>';
                $('#clientDocumentsTableBody').append(row);
            });
        }).catch(function () {
            $('#documentsCountText').text('Error loading');
        }).finally(function () {
            $('#documentsLoading').hide();
        });
    }

    $(document).on('click', '.client-documents-btn', function () {
        var clientId = $(this).data('client-id');
        var clientName = $(this).data('client-name') || 'Client';
        var offcanvas = new bootstrap.Offcanvas(document.getElementById('clientDocumentsOffcanvas'));
        offcanvas.show();
        loadClientDocuments(clientId, clientName);
    });

    $('#clientDocumentsAddBtn').on('click', function () {
        if (!currentDocumentsClientId) return;
        $('#uploadClientId').val(currentDocumentsClientId);
        $('#documentUploadForm')[0].reset();
        $('#documentUploadProgress, #documentUploadError').hide();
        var modal = new bootstrap.Modal(document.getElementById('documentUploadModal'));
        modal.show();
    });

    $('#documentUploadForm').on('submit', function (e) {
        e.preventDefault();
        var clientId = $('#uploadClientId').val();
        if (!clientId) return;
        var formData = new FormData(this);
        formData.append('_token', $('meta[name="csrf-token"]').attr('content'));
        var $btn = $('#documentUploadSubmitBtn');
        var $progress = $('#documentUploadProgress');
        var $bar = $progress.find('.progress-bar');
        var $err = $('#documentUploadError');
        $btn.prop('disabled', true).find('.spinner-border').show();
        $err.hide();
        $progress.show().find('.progress-bar').css('width', '10%').text('10%');
        var url = docsStoreUrlBase.replace('CID', clientId);
        axios.post(url, formData, {
            headers: { 'Content-Type': 'multipart/form-data' },
            onUploadProgress: function (ev) {
                var pct = Math.round((ev.loaded / ev.total) * 100);
                $bar.css('width', pct + '%').text(pct + '%');
            }
        }).then(function (res) {
            if (res.data.success) {
                bootstrap.Modal.getInstance(document.getElementById('documentUploadModal')).hide();
                loadClientDocuments(currentDocumentsClientId, $('#clientDocumentsOffcanvasLabel').text().replace(' - Documents', ''));
                successToast(res.data.msg);
                $('.relode-btn').trigger('click');
            } else {
                $err.text(res.data.msg || 'Upload failed').show();
            }
        }).catch(function (err) {
            var msg = (err.response && err.response.data && err.response.data.msg) ? (Array.isArray(err.response.data.msg) ? err.response.data.msg[0] : err.response.data.msg) : 'Upload failed';
            $err.text(msg).show();
        }).finally(function () {
            $btn.prop('disabled', false).find('.spinner-border').hide();
            $progress.hide();
        });
    });

    $(document).on('click', '.doc-delete-btn', function () {
        var docId = $(this).data('doc-id');
        var docName = $(this).data('doc-name');
        
        if (!confirm('Delete "' + docName + '"?')) return;
        
        var url = docsDestroyUrlBase.replace('DID', docId);
        
        axios.delete(url, { headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }, data: {} }).then(function (res) {
            if (res.data.success) {
                loadClientDocuments(currentDocumentsClientId, $('#clientDocumentsOffcanvasLabel').text().replace(' - Documents', ''));
                $('.relode-btn').trigger('click');
                successToast(res.data.msg || 'Deleted');
            }
        });
    });
});
</script>
@endpush
