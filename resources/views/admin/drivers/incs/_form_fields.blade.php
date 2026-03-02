@php
    $prefix = $prefix ?? '';
    $clientId = $prefix ? $prefix . 'client_id' : 'client_id';
    $vehicleId = $prefix ? $prefix . 'vehicle_id' : 'vehicle_id';
@endphp

<div class="my-3 row g-2">
    <label for="{{ $prefix }}name" class="col-12 col-md-2 col-form-label">@lang('drivers.Name') <span class="text-danger">*</span></label>
    <div class="col-12 col-md-10">
        <input type="text" class="form-control" id="{{ $prefix }}name">
        <div class="err-msg mt-2 alert alert-danger py-2" style="display: none;" id="{{ $prefix }}nameErr"></div>
    </div>
</div>

<div class="my-3 row g-2">
    <label for="{{ $prefix }}email" class="col-12 col-md-2 col-form-label">@lang('drivers.Email') <span class="text-danger">*</span></label>
    <div class="col-12 col-md-10">
        <input type="email" class="form-control" id="{{ $prefix }}email">
        <div class="err-msg mt-2 alert alert-danger py-2" style="display: none;" id="{{ $prefix }}emailErr"></div>
    </div>
</div>

<div class="my-3 row g-2">
    <label for="{{ $prefix }}phone" class="col-12 col-md-2 col-form-label">@lang('drivers.Phone') <span class="text-danger">*</span></label>
    <div class="col-12 col-md-10">
        <input type="text" class="form-control" id="{{ $prefix }}phone">
        <div class="err-msg mt-2 alert alert-danger py-2" style="display: none;" id="{{ $prefix }}phoneErr"></div>
    </div>
</div>

<div class="my-3 row g-2">
    <label for="{{ $prefix }}password" class="col-12 col-md-2 col-form-label">@lang('drivers.Password') @if(!$prefix)<span class="text-danger">*</span>@endif</label>
    <div class="col-12 col-md-10">
        <input type="password" class="form-control" id="{{ $prefix }}password" @if($prefix) placeholder="@lang('drivers.Password')" @endif>
        <div class="err-msg mt-2 alert alert-danger py-2" style="display: none;" id="{{ $prefix }}passwordErr"></div>
    </div>
</div>

<div class="my-3 row g-2">
    <label for="{{ $clientId }}" class="col-12 col-md-2 col-form-label">@lang('drivers.Client') <span class="text-danger">*</span></label>
    <div class="col-12 col-md-10">
        <select class="form-control form-select select2-driver-client" id="{{ $clientId }}" style="width: 100%;" data-prefix="{{ $prefix }}"></select>
        <div class="err-msg mt-2 alert alert-danger py-2" style="display: none;" id="{{ $clientId }}Err"></div>
    </div>
</div>

<div class="my-3 row g-2">
    <label for="{{ $vehicleId }}" class="col-12 col-md-2 col-form-label">@lang('drivers.Vehicle')</label>
    <div class="col-12 col-md-10">
        <select class="form-control form-select select2-driver-vehicle" id="{{ $vehicleId }}" style="width: 100%;" data-prefix="{{ $prefix }}"></select>
        <div class="err-msg mt-2 alert alert-danger py-2" style="display: none;" id="{{ $vehicleId }}Err"></div>
    </div>
</div>

<div class="my-3 row g-2">
    <label for="{{ $prefix }}picture" class="col-12 col-md-2 col-form-label">@lang('drivers.Picture')</label>
    <div class="col-12 col-md-10">
        <input type="file" class="form-control" id="{{ $prefix }}picture" accept="image/*">
    </div>
</div>
