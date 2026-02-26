<div class="modal fade" id="proofImageModal" tabindex="-1" aria-labelledby="proofImageModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header py-2">
                <h5 class="modal-title" id="proofImageModalLabel">@lang('deposit_requests.Proof Image')</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-g text-center bg-dark" style="min-height: 200px">
                <img id="proofImageModalImg" src="" alt="@lang('deposit_requests.Proof Image')" class="img-fluid" style="max-height: 80vh;">
            </div>
        </div>
    </div>
</div>


@push('custome-js-2')
<script>
$(document).ready(function () {
    
    $(document).on('click', '.deposit-proof-btn', function () {
        var url = $(this).data('url');
        if (url) {
            $('#proofImageModalImg').attr('src', url);
            new bootstrap.Modal(document.getElementById('proofImageModal')).show();
        }
    });

});
</script>
@endpush