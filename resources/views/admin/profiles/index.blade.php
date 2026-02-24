@extends('layouts.admin.app')

@section('content')
<div dir="{{ LaravelLocalization::getCurrentLocale() == 'ar' ? 'rtl' : 'ltr' }}" class="text-left">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">@lang('users.Profile')</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-end">
                        <li class="breadcrumb-item">
                            <a href="{{ url('admin') }}">@lang('layouts.Dashboard')</a>
                        </li>
                        
                        <li class="breadcrumb-item active">
                            @lang('layouts.Profile')
                        </li>
                    </ol>
                </div>
            </div><!-- /.row -->
        </div><!-- /.container-fluid -->
    </div><!-- /.content-header -->

    <div class="container-fluid">

        <div id="successAlert" style="display: none" class="alert alert-success"></div>
        
        <div id="dangerAlert"  style="display: none" class="alert alert-danger"></div>
            
        <div id="warningAlert" style="display: none" class="alert alert-warning"></div>

        <div class="d-flex justify-content-center mb-3">
            <div id="loddingSpinner" style="display: none" class="spinner-border" role="status">
                <span class="sr-only">Loading...</span>
            </div>
        </div>

        <div id="objectsCard" class="card card-body">
            <div class="row mb-4">
                <div class="col-6">
                    <h5>@lang('users.Account') <span class="text-primary mx-2">"{{$target_user->name}}"</span></h5>
                </div>
                <div class="col-6 text-end">
                    @if(auth()->user()->hasRole('admin') || auth()->user()->isAbleTo(['users_add']) || $target_user->id == auth()->user()->id )
                    <div class="toggle-btn btn btn-warning btn-sm" data-current-card="#objectsCard" data-target-card="#editObjectCard">
                        <i class="fas fa-edit"></i>
                    </div>
                    @endif
                </div>
            </div><!-- /.row -->
            
            <!-- START SEARCH BAR -->
            <div class="">
                <table class="table">
                    <tr>
                        <td>@lang('users.Email')</td>
                        <td>{{isset($target_user->email) ? $target_user->email : '---'}}</td>
                    </tr>
                    <tr>
                        <td>@lang('users.Phone')</td>
                        <td>{{isset($target_user->phone) ? $target_user->phone : '---'}}</td>
                    </tr>
                    <tr>
                        <td>@lang('users.Category')</td>
                        <td>{{isset($target_user->category) ? $target_user->category : '---'}}</td>
                    </tr>
                    <tr>
                        <td>@lang('users.Role')</td>
                        <td>
                            @if(sizeof($target_user->roles))
                                @foreach($target_user->roles as $role)
                                <span class="badge badge-pill bg-primary">{{ $role->name }}</span>
                                @endforeach
                            @else 
                                --- 
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td>@lang('users.Permissions')</td>
                        <td>
                            @if(sizeof($target_user->permissions))
                                @foreach($target_user->permissions as $permission)
                                <span class="badge badge-pill bg-primary">{{ $permission->display_name }}</span>
                                @endforeach
                            @else 
                                --- 
                            @endif
                        </td>
                    </tr>
                </table>
            </div>
        </div><!-- /.card --> 

        @include('admin.profiles.incs._edit')
        
    </div>
</div>
@endsection

@push('custome-js')
<script>
$(document).ready(function () {
    $('.toggle-btn').click(function () {
        let target_card = $(this).data('target-card');
        let current_card = $(this).data('current-card');

        $(target_card).slideDown(500);
        $(current_card).slideUp(500);
        $('#dangerAlert').html('').slideUp(500);
    });
});
</script>
@endpush