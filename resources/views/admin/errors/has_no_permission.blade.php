@extends('layouts.admin.app')

@section('content')
<div class="container-fluid pt-4">
    <div class="!card !card-body text-center pb-3">
        <img style="width: 30%; margin: auto;" class="img-thumbnail" src="{{ asset('images/no_permissions.webp') }}" alt="">
        <h3 class="mt-3 text-center">@lang('layouts.has_no_permissions')</h3>
    </div>
</div><!-- /.container-fluid -->
@endsection