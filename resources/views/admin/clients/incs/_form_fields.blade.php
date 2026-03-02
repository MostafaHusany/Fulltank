@php
    $prefix = $prefix ?? '';
@endphp

<div class="my-3 row g-2">
    <label for="{{ $prefix }}name" class="col-12 col-md-2 col-form-label">@lang('clients.Name') <span class="text-danger">*</span></label>
    <div class="col-12 col-md-10">
        <input type="text" class="form-control" id="{{ $prefix }}name">
        <div class="err-msg mt-2 alert alert-danger py-2" id="{{ $prefix }}nameErr" style="display: none;"></div>
    </div>
</div>

<div class="my-3 row g-2">
    <label for="{{ $prefix }}company_name" class="col-12 col-md-2 col-form-label">@lang('clients.Company Name') <span class="text-danger">*</span></label>
    <div class="col-12 col-md-10">
        <input type="text" class="form-control" id="{{ $prefix }}company_name">
        <div class="err-msg mt-2 alert alert-danger py-2" id="{{ $prefix }}company_nameErr" style="display: none;"></div>
    </div>
</div>

<div class="my-3 row g-2">
    <label for="{{ $prefix }}client_category" class="col-12 col-md-2 col-form-label">@lang('clients.Client Type') <span class="text-danger">*</span></label>
    <div class="col-12 col-md-10">
        <select class="form-control select2-category" id="{{ $prefix }}client_category" style="width: 100%;"></select>
        <div class="err-msg mt-2 alert alert-danger py-2" id="{{ $prefix }}client_categoryErr" style="display: none;"></div>
    </div>
</div>

<div class="my-3 row g-2">
    <label for="{{ $prefix }}email" class="col-12 col-md-2 col-form-label">@lang('clients.Email') <span class="text-danger">*</span></label>
    <div class="col-12 col-md-10">
        <input type="email" class="form-control" id="{{ $prefix }}email">
        <div class="err-msg mt-2 alert alert-danger py-2" id="{{ $prefix }}emailErr" style="display: none;"></div>
    </div>
</div>

<div class="my-3 row g-2">
    <label for="{{ $prefix }}phone" class="col-12 col-md-2 col-form-label">@lang('clients.Phone') <span class="text-danger">*</span></label>
    <div class="col-12 col-md-10">
        <input type="text" class="form-control" id="{{ $prefix }}phone">
        <div class="err-msg mt-2 alert alert-danger py-2" id="{{ $prefix }}phoneErr" style="display: none;"></div>
    </div>
</div>

<div class="my-3 row g-2">
    <label for="{{ $prefix }}password" class="col-12 col-md-2 col-form-label">@lang('clients.Password') @if(!$prefix)<span class="text-danger">*</span>@endif</label>
    <div class="col-12 col-md-10">
        <input type="password" class="form-control" id="{{ $prefix }}password" @if($prefix) placeholder="@lang('clients.Password')" @endif>
        <div class="err-msg mt-2 alert alert-danger py-2" id="{{ $prefix }}passwordErr" style="display: none;"></div>
    </div>
</div>

<div class="my-3 row g-2">
    <label for="{{ $prefix }}picture" class="col-12 col-md-2 col-form-label">@lang('clients.Picture')</label>
    <div class="col-12 col-md-10">
        <input type="file" class="form-control" id="{{ $prefix }}picture" accept="image/*">
    </div>
</div>
