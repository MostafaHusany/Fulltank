@php 
    $lang = LaravelLocalization::getCurrentLocale();
@endphp

<!doctype html>
<html lang="{{ $lang == 'ar' ? 'ar' : 'en' }}" dir="{{ $lang == 'ar' ? 'rtl' : 'ltr' }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }} - @lang('client.panel_title')</title>

        @if ($lang == 'ar')
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.rtl.min.css" integrity="sha384-DOXMLfHhQkvFFp+rWTZwVlPVqdIhpDVYT9csOnHSgWQWPX0v5MCGtjCJbY6ERspU" crossorigin="anonymous">
        @else
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
        @endif

        <link rel="dns-prefetch" href="//fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=Nunito" rel="stylesheet">

        <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
        <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/toastify-js"></script>

        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.8.2/css/all.min.css" integrity="sha512-rqQltXRuHxtPWhktpAZxLHUVJ3Eombn3hvk9PHjV/N5DMUYnzKPC1i3ub0mEXgFzsaZNeJcoE0YHq0j/GFsdGg==" crossorigin="anonymous" referrerpolicy="no-referrer" />

        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js" integrity="sha512-v2CJ7UaYy4JwqLDIrZUI/4hqeoQieOmAZNXBeQyjo21dadnwR+8ZaIJVT8EE2iyI61OV8e6M8PP2/4hpQINQ/g==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>

        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" integrity="sha512-nMNlpuaDPrqlEls3IX/Q56H36qvBASwb3ipuo3MxeWbsQB1881ox0cRv7UPTgBlriqoynt35KjEwgGUeUXIPnw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
        <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js" integrity="sha512-2ImtlRlf2VVmiGZsjm9bEyhjGW4dU7B6TNwh/hx/iSByxNENtj3WVE6o/9Lj4TJeVXPi4bnOIMXFIJJAeufa0A==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>

        @vite(['resources/sass/app.scss', 'resources/js/app.js'])
        
        @if ($lang == 'ar')
        <link href="{{ asset('css/admin/rtl_main.css') }}" rel="stylesheet">
        @else
        <link href="{{ asset('css/admin/ltr_main.css') }}" rel="stylesheet">
        @endif
        
        @stack('custome-plugin')
        @stack('custome-css')
    </head>
    <body>
    
        @include('layouts.clients.incs._header')

        <div class="container-fluid">
            <div class="row">
                @include('layouts.clients.incs._navbar')
                
                <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 pb-4">
                    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                        @stack('title')
                        <div class="d-flex justify-content-center mb-2">
                            <div id="loddingSpinner" style="display: none" class="spinner-border" role="status">
                                <span class="sr-only">Loading...</span>
                            </div>
                        </div>
                    </div>
                    @yield('content')
                </main>
            </div>
        </div>

        <script>
            window.lang  = "{{ \LaravelLocalization::getCurrentLocale() }}";
            window.is_ar = lang == 'ar';
            window.base_url = "{{ url('/') }}";
            window.loddingSpinnerEl = $('#loddingSpinner');

            window.successToast = (msg) => {
                Toastify({
                    text: msg,
                    className: "info",
                    offset: { x: 20, y: 50 },
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
                    offset: { x: 20, y: 50 },
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
        
        {{-- Notification System --}}
        <script>
        (function() {
            var lastCheckTime = null;
            var notificationPollInterval = 30000;
            
            function loadNotifications() {
                $.ajax({
                    url: '{{ route("client.notifications.latest") }}',
                    method: 'GET',
                    success: function(res) {
                        updateNotificationUI(res.notifications, res.unread_count);
                    }
                });
            }
            
            function updateNotificationUI(notifications, unreadCount) {
                var $badge = $('#notificationCount');
                var $list = $('#notificationList');
                var $noNotifications = $('#noNotifications');
                
                if (unreadCount > 0) {
                    $badge.text(unreadCount > 99 ? '99+' : unreadCount).show();
                } else {
                    $badge.hide();
                }
                
                if (notifications.length === 0) {
                    $noNotifications.show();
                    return;
                }
                
                $noNotifications.hide();
                $list.empty();
                
                notifications.forEach(function(n) {
                    var readClass = n.read ? 'bg-white' : 'bg-light';
                    var colorClass = 'text-' + n.color;
                    
                    var html = '<a href="' + n.link + '" class="dropdown-item d-flex align-items-start py-2 border-bottom ' + readClass + '" data-id="' + n.id + '">' +
                        '<div class="flex-shrink-0 me-2">' +
                            '<i class="fas ' + n.icon + ' ' + colorClass + '"></i>' +
                        '</div>' +
                        '<div class="flex-grow-1">' +
                            '<div class="fw-semibold small ' + colorClass + '">' + n.title + '</div>' +
                            '<p class="mb-0 small text-muted" style="white-space: normal;">' + n.message + '</p>' +
                            '<small class="text-muted">' + n.created_at + '</small>' +
                        '</div>' +
                    '</a>';
                    
                    $list.append(html);
                });
            }
            
            function checkNewNotifications() {
                $.ajax({
                    url: '{{ route("client.notifications.new") }}',
                    method: 'GET',
                    data: { last_check: lastCheckTime },
                    success: function(res) {
                        lastCheckTime = res.server_time;
                        
                        if (res.notifications.length > 0) {
                            res.notifications.forEach(function(n) {
                                showNotificationToast(n);
                            });
                            loadNotifications();
                        }
                    }
                });
            }
            
            function showNotificationToast(notification) {
                var bgColor = '#0d6efd';
                if (notification.color === 'danger') bgColor = '#dc3545';
                else if (notification.color === 'warning') bgColor = '#ffc107';
                else if (notification.color === 'success') bgColor = '#198754';
                
                Toastify({
                    text: notification.title + ': ' + notification.message,
                    duration: 5000,
                    close: true,
                    gravity: "top",
                    position: is_ar ? "left" : "right",
                    style: {
                        background: bgColor,
                        color: notification.color === 'warning' ? '#000' : '#fff'
                    },
                    onClick: function() {
                        if (notification.link) {
                            window.location.href = notification.link;
                        }
                    }
                }).showToast();
            }
            
            $(document).ready(function() {
                loadNotifications();
                
                lastCheckTime = new Date().toISOString();
                setInterval(checkNewNotifications, notificationPollInterval);
                
                $('#markAllRead').on('click', function(e) {
                    e.preventDefault();
                    $.ajax({
                        url: '{{ route("client.notifications.markAllRead") }}',
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                        success: function() {
                            loadNotifications();
                        }
                    });
                });
                
                $(document).on('click', '#notificationList a.dropdown-item', function() {
                    var id = $(this).data('id');
                    $.ajax({
                        url: '{{ url("client/notifications") }}/' + id + '/read',
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
                    });
                });
            });
        })();
        </script>
        
        <style>
            .notification-dropdown .dropdown-item:hover {
                background-color: #f8f9fa !important;
            }
            .notification-badge {
                font-size: 0.65rem;
                padding: 0.25em 0.5em;
            }
            @keyframes bellShake {
                0%, 100% { transform: rotate(0); }
                25% { transform: rotate(15deg); }
                50% { transform: rotate(-15deg); }
                75% { transform: rotate(10deg); }
            }
            .notification-badge:not([style*="display: none"]) ~ i,
            #notificationBell:has(.notification-badge:not([style*="display: none"])) i {
                animation: bellShake 0.5s ease-in-out;
            }
        </style>
    </body>
</html>
