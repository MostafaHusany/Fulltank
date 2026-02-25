@php
    $prefix = $prefix ?? '';
    $id = $prefix ? $prefix . 'client_id' : 'client_id';
@endphp

<div class="my-3 row g-2">
    <label for="{{ $id }}" class="col-12 col-md-2 col-form-label">@lang('vehicles.Client') <span class="text-danger">*</span></label>
    <div class="col-12 col-md-10">
        <select class="form-control form-select select2-client" id="{{ $id }}" style="width: 100%;" data-prefix="{{ $prefix }}"></select>
        <div class="err-msg mt-2 alert alert-danger d-none" id="{{ $id }}Err"></div>
    </div>
</div>

<div class="my-3 row g-2">
    <label for="{{ $prefix }}plate_number" class="col-12 col-md-2 col-form-label">@lang('vehicles.Plate Number') <span class="text-danger">*</span></label>
    <div class="col-12 col-md-10">
        <input type="text" class="form-control" id="{{ $prefix }}plate_number" placeholder="ABC 1234">
        <small class="text-muted">@lang('vehicles.Plate Format Hint')</small>
        <div class="err-msg mt-2 alert alert-danger d-none" id="{{ $prefix }}plate_numberErr"></div>
    </div>
</div>

<div class="my-3 row g-2">
    <label for="{{ $prefix }}model" class="col-12 col-md-2 col-form-label">@lang('vehicles.Model') <span class="text-danger">*</span></label>
    <div class="col-12 col-md-10">
        <input type="text" class="form-control" id="{{ $prefix }}model">
        <div class="err-msg mt-2 alert alert-danger d-none" id="{{ $prefix }}modelErr"></div>
    </div>
</div>

<div class="my-3 row g-2">
    <label for="{{ $prefix }}fuel_type" class="col-12 col-md-2 col-form-label">@lang('vehicles.Fuel Type') <span class="text-danger">*</span></label>
    <div class="col-12 col-md-10">
        <select class="form-select form-control" id="{{ $prefix }}fuel_type">
            @if(!$prefix)
            <option value="">-- @lang('layouts.all') --</option>
            @endif
            <option value="petrol">@lang('vehicles.Petrol')</option>
            <option value="diesel">@lang('vehicles.Diesel')</option>
            <option value="electric">@lang('vehicles.Electric')</option>
            <option value="hybrid">@lang('vehicles.Hybrid')</option>
            <option value="cng">@lang('vehicles.CNG')</option>
        </select>
        <div class="err-msg mt-2 alert alert-danger d-none" id="{{ $prefix }}fuel_typeErr"></div>
    </div>
</div>
