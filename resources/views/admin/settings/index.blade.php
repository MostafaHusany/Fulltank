@extends('layouts.admin.app')

@push('title')
    <h1 class="h2">@lang('layouts.Settings')</h1>
@endpush

@push('custome-plugin')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" integrity="sha512-xodZBNTC5n17Xt2atTPuE1HxjVMSvLVW9ocqUKLsCC5CXdbqCmblAshOMAS6/keqq/sMZMZ19scR4PsZChSR7A==" crossorigin="" />
<script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js" integrity="sha512-XQoYMqMTK8LvdxXYG3nZ448hOEQiglfqkJs1NOQV44cWnUrBc8PkAOcXy20w0vlaXaVUearIOBhiXZ5V3ynxwA==" crossorigin=""></script>
@endpush

@section('content')
    <nav>
        <div class="nav nav-tabs" id="nav-tab" role="tablist">
            <button class="nav-link active" id="nav-truckers-tab" data-bs-toggle="tab" data-bs-target="#nav-truckers" type="button" role="tab" aria-controls="nav-truckers" aria-selected="true">@lang('settings.Truckers Settings')</button>
        </div>
    </nav><!-- /.nav -->

    <div class="tab-content mt-3" id="nav-tabContent">
        <div class="tab-pane fade show active" id="nav-truckers" role="tabpanel" aria-labelledby="nav-truckers-tab" tabindex="0">
            @include('admin.settings.incs._truckers_settings')
        </div><!-- /.tab-pane -->
    </div><!-- /.tab-content -->

@endSection

@push('custome-js')
<script>
    $('document').ready(function () {
        

        const init = (() => {
            window.lang = "{{ $lang }}";


        })();

    });
</script>
@endpush