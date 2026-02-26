@extends('layouts.admin.app')

@push('title')
    <h1 class="h2">@lang('deposit_requests.Financial Settings')</h1>
@endpush

@section('content')
    <div class="card">
        <div class="card-header">
            <ul class="nav nav-tabs card-header-tabs" role="tablist">
                <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#tabPaymentAccounts">@lang('deposit_requests.Payment Accounts')</a></li>
                <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tabServiceFees">@lang('deposit_requests.Service Fees')</a></li>
            </ul>
        </div>
        <div class="card-body">
            <div class="tab-content">
                <div id="tabPaymentAccounts" class="tab-pane fade show active">
                    @include('admin.financial_settings.incs._payment_accounts')
                </div>
                <div id="tabServiceFees" class="tab-pane fade">
                    @include('admin.financial_settings.incs._service_fees')
                </div>
            </div>
        </div>
    </div>
@endsection
