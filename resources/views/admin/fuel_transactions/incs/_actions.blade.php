@php
    $is_ar = LaravelLocalization::getCurrentLocale() == 'ar';
@endphp

<div class="text-center">
    @if($permissions == 'admin' || sizeof($permissions) > 0)
    <div class="btn-group">
        <button type="button" class="btn btn-outline-dark btn-sm dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="fas fa-sliders-h"></i>
        </button>
        <div class="dropdown-menu {{ !$is_ar ? '!dropdown-menu-right !dropdown-menu-lg-right' : '!dropdown-menu-left !dropdown-menu-lg-left' }}">

            @if($row->status === 'pending' && ($permissions == 'admin' || in_array('fuelTransactions_edit', $permissions)))
            <button class="dropdown-item cancel-transaction-btn text-secondary"
                data-transaction-id="{{ $row->id }}"
            >
                <div class="row">
                    <div class="col-8 text-left">
                        <span>@lang('fuel_transactions.Cancel')</span>
                    </div>
                    <div class="col-4">
                        <i class="fas fa-times float-end"></i>
                    </div>
                </div>
            </button>
            @endif

            @if($row->status === 'completed' && ($permissions == 'admin' || in_array('fuelTransactions_edit', $permissions)))
            <button class="dropdown-item refund-btn text-info"
                data-transaction-id="{{ $row->id }}"
                data-ref-no="{{ $row->reference_no }}"
            >
                <div class="row">
                    <div class="col-8 text-left">
                        <span>@lang('fuel_transactions.Refund')</span>
                    </div>
                    <div class="col-4">
                        <i class="fas fa-undo float-end"></i>
                    </div>
                </div>
            </button>
            @endif

            @if($row->status === 'refunded' && $row->refund_reason)
            <div class="dropdown-item text-muted" style="white-space: normal; max-width: 250px;">
                <small>
                    <strong>@lang('fuel_transactions.Refund Reason'):</strong><br>
                    {{ $row->refund_reason }}
                </small>
            </div>
            @endif

            @if(!in_array($row->status, ['pending', 'completed']))
            <div class="dropdown-item text-muted">
                <small>@lang('fuel_transactions.No actions available')</small>
            </div>
            @endif

        </div>
    </div>
    @else
    <span>---</span>
    @endif
</div>
