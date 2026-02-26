@php
    $canReview = $row->status === 'pending' && ($permissions == 'admin' || in_array('depositRequests_edit', $permissions ?? []));
    $canGenerate = $row->status === 'approved' && !$row->wallet_transaction_id && ($permissions == 'admin' || in_array('depositRequests_edit', $permissions ?? []));
    $canShowGenerated = $row->wallet_transaction_id && ($permissions == 'admin' || in_array('depositRequests_show', $permissions ?? []) || in_array('depositRequests_edit', $permissions ?? []));
    $isRejected = $row->status === 'rejected';
@endphp
<div class="d-flex flex-wrap gap-1 justify-content-center">
    @if($canReview)
    <div class="dropdown">
        <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle deposit-review-dropdown" data-id="{{ $row->id }}" data-bs-toggle="dropdown" aria-expanded="false">@lang('deposit_requests.Review')</button>
        <ul class="dropdown-menu dropdown-menu-end">
            <li><a class="dropdown-item deposit-approve-btn text-success" href="#" data-id="{{ $row->id }}"><i class="fas fa-check me-1"></i>@lang('deposit_requests.Approve')</a></li>
            <li><a class="dropdown-item deposit-reject-btn text-danger" href="#" data-id="{{ $row->id }}"><i class="fas fa-times me-1"></i>@lang('deposit_requests.Reject')</a></li>
        </ul>
    </div>
    @endif
    
    @if($canGenerate)
    <button type="button" class="btn btn-sm btn-outline-primary deposit-generate-btn" data-id="{{ $row->id }}">@lang('deposit_requests.Generate Balance')</button>
    @endif

    @if($canShowGenerated)
    <button type="button" class="btn btn-sm btn-outline-info deposit-show-generated-btn" data-id="{{ $row->id }}">@lang('deposit_requests.Show Generated')</button>
    @endif

    @if($isRejected)
    <span>---</span>
    @endif
</div>
