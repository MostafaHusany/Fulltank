@extends('layouts.basic')

@section('content')
<nav class="navbar navbar-expand-lg navbar-light bg-light">
    <div class="container">
        <a class="navbar-brand" href="{{ url('/') }}">
            <img style="border-radius: 50%;" class="animation__shake" src="{{ asset('images/AdminLTELogo.png') }}" alt="AdminLTELogo" height="40" width="40">
        </a>

        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav ml-auto">
                
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-user-cog"></i>
                    </a>
                    <div class="dropdown-menu">
                        <div class="dropdown-divider"></div>
                        <a href="#" class="dropdown-item"
                            onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                            @lang('layouts.Sign out')
                        </a>
                        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                            @csrf
                        </form>
                    </div>
                </li>
            </ul>
        </div>
    </div><!-- /.container -->
</nav>

<div class="container" style="height: 100vh">
    <div class="row justify-content-center mt-5 !align-items-center" style="height: 100vh">
        <div class="col-12">
            <div class="alert alert-danger text-center">
                <i class="fas fa-exclamation-triangle fa-5x mb-2"></i>
                <h1>@lang('layouts.disabled_acc')</h1>
            </div>
        </div>
    </div>
</div><!-- /.container -->
@endsection