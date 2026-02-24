@php 
    $lang = LaravelLocalization::getCurrentLocale();
@endphp

<!doctype html>
<html lang="{{ $lang == 'ar' ? 'ar' : 'en' }}" dir="{{ $lang == 'ar' ? 'rtl' : 'ltr' }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        
        <!-- CSRF Token -->
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Bootstrap -->
        @if ($lang == 'ar')
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.rtl.min.css" integrity="sha384-DOXMLfHhQkvFFp+rWTZwVlPVqdIhpDVYT9csOnHSgWQWPX0v5MCGtjCJbY6ERspU" crossorigin="anonymous">
        @else
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
        @endif

        <!-- Fonts -->
        <link rel="dns-prefetch" href="//fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=Nunito" rel="stylesheet">

        <!-- Toastify -->
        <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
        <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/toastify-js"></script>

        <!-- Font-Awesome -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.8.2/css/all.min.css" integrity="sha512-rqQltXRuHxtPWhktpAZxLHUVJ3Eombn3hvk9PHjV/N5DMUYnzKPC1i3ub0mEXgFzsaZNeJcoE0YHq0j/GFsdGg==" crossorigin="anonymous" referrerpolicy="no-referrer" />

        <!-- jQuery -->
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js" integrity="sha512-v2CJ7UaYy4JwqLDIrZUI/4hqeoQieOmAZNXBeQyjo21dadnwR+8ZaIJVT8EE2iyI61OV8e6M8PP2/4hpQINQ/g==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>

        <!-- Select2 -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" integrity="sha512-nMNlpuaDPrqlEls3IX/Q56H36qvBASwb3ipuo3MxeWbsQB1881ox0cRv7UPTgBlriqoynt35KjEwgGUeUXIPnw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
        <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js" integrity="sha512-2ImtlRlf2VVmiGZsjm9bEyhjGW4dU7B6TNwh/hx/iSByxNENtj3WVE6o/9Lj4TJeVXPi4bnOIMXFIJJAeufa0A==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>

        <!-- Scripts -->
        @vite(['resources/sass/app.scss', 'resources/js/app.js'])
        
        <!-- Custom styles for this template -->
        @if ($lang == 'ar')
        <link href="{{ asset('css/admin/rtl_main.css') }}" rel="stylesheet">
        @else
        <link href="{{ asset('css/admin/ltr_main.css') }}" rel="stylesheet">
        @endif
        
        @stack('custome-plugin')

        @stack('custome-css')

    </head>
    <body>
    
        @include('layouts.admin.incs._header')

        <div class="container-fluid">
            <div class="row">
                @include('layouts.admin.incs._navbar')
                
                <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 pb-4">

                    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                        @stack('title')

                        <div class="d-flex justify-content-center mb-2">
                            <div id="loddingSpinner" style="display: none" class="spinner-border" role="status">
                                <span class="sr-only">Loading...</span>
                            </div>
                        </div>

                        {{--
                            <h1 class="h2">Dashboard</h1>

                            <div class="btn-toolbar mb-2 mb-md-0">
                                <div class="btn-group me-2">
                                    <button type="button" class="btn btn-sm btn-outline-secondary">Share</button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary">Export</button>
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle">
                                    <span data-feather="calendar" class="align-text-bottom"></span>
                                    This week
                                </button><!-- /.btn-toolbar -->
                            </div>
                        --}}
                    </div>

                    
                    @yield('content')

                </main>
            </div><!-- /.row -->
        </div><!-- /.container-fluid -->


        <script>
            window.lang  = "{{ \LaravelLocalization::getCurrentLocale() }}";
            window.is_ar = lang == 'ar';
            window.base_url = "{{ url('/') }}";
            window.loddingSpinnerEl = $('#loddingSpinner');

            window.successToast = (msg) => {
                Toastify({
                    text: msg,
                    className: "info",
                    offset: {
                        x: 20, // horizontal axis - can be a number or a string indicating unity. eg: '2em'
                        y: 50 // vertical axis - can be a number or a string indicating unity. eg: '2em'
                    },
                    style: {
                        color: '#0f5132',
                        background: '#d1e7dd',
                        borderColor: '#badbcc'
                    }
                }).showToast();
            };

            window.failerToast = (msg) => {
                Toastify({
                    text: msg,
                    className: "info",
                    offset: {
                        x: 20, // horizontal axis - can be a number or a string indicating unity. eg: '2em'
                        y: 50 // vertical axis - can be a number or a string indicating unity. eg: '2em'
                    },
                    style: {
                        color: '#842029',
                        background: '#f8d7da',
                        borderColor: '#f5c2c7'
                    }
                }).showToast();
            };

            window.toggleBtn = (thisObj, open = true) => {
                if (open) {
                    $(thisObj).attr('disabled', 'disabled');
                    $(loddingSpinnerEl).fadeIn(500);
                } else {
                    $(thisObj).removeAttr('disabled');
                    $(loddingSpinnerEl).fadeOut(500);
                }
            }


        </script>

               
        @stack('custome-js') 
        @stack('custome-js-2')

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ka7Sk0Gln4gmtz2MlQnikT1wXgYsOg+OMhuP+IlRH9sENBO0LRn5q+8nbTov4+1p" crossorigin="anonymous"></script>
    </body>
</html>
