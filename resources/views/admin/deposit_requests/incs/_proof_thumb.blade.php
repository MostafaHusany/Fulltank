@if($row->proof_image)
    @php $url = route('admin.depositRequests.viewProofImage', ['id' => $row->id]); @endphp
    <button type="button" class="btn btn-sm btn-outline-info deposit-proof-btn" data-id="{{ $row->id }}" data-url="{{ $url }}" title="@lang('deposit_requests.Proof Image')">
        <i class="fas fa-image"></i>
    </button>
@else
    —
@endif
