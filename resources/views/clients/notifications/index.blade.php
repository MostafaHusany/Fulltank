@extends('layouts.clients.app')

@push('title')
    <h4 class="h4">@lang('notifications.title')</h4>
@endpush

@push('custome-css')
<style>
    .notification-card {
        transition: all 0.2s ease;
        border-left: 4px solid transparent;
    }
    .notification-card:hover {
        background-color: #f8f9fa;
    }
    .notification-card.unread {
        background-color: #f0f7ff;
    }
    .notification-card.type-danger {
        border-left-color: #dc3545;
    }
    .notification-card.type-warning {
        border-left-color: #ffc107;
    }
    .notification-card.type-success {
        border-left-color: #198754;
    }
    .notification-card.type-info,
    .notification-card.type-primary {
        border-left-color: #0d6efd;
    }
    .notification-icon {
        width: 45px;
        height: 45px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
    }
    .notification-icon.bg-danger { background-color: rgba(220, 53, 69, 0.15); }
    .notification-icon.bg-warning { background-color: rgba(255, 193, 7, 0.15); }
    .notification-icon.bg-success { background-color: rgba(25, 135, 84, 0.15); }
    .notification-icon.bg-info { background-color: rgba(13, 110, 253, 0.15); }
</style>
@endpush

@section('content')
<div class="container-fluid">
    {{-- Header Actions --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <span class="text-muted">
                @lang('notifications.total_notifications'): <strong>{{ $notifications->total() }}</strong>
            </span>
        </div>
        <div>
            <form action="{{ route('client.notifications.markAllRead') }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-outline-primary btn-sm">
                    <i class="fas fa-check-double me-1"></i>@lang('notifications.mark_all_read')
                </button>
            </form>
        </div>
    </div>

    {{-- Notifications List --}}
    @forelse($notifications as $notification)
        @php
            $data = $notification->data;
            $type = $data['type'] ?? 'info';
            $icon = $data['icon'] ?? 'fa-bell';
            $color = $data['color'] ?? 'primary';
            $title = $data['title'] ?? '';
            $message = $data['message'] ?? '';
            $link = $data['link'] ?? '#';
            $isUnread = $notification->read_at === null;
        @endphp
        
        <div class="card notification-card mb-2 {{ $isUnread ? 'unread' : '' }} type-{{ $color }}">
            <div class="card-body py-3">
                <div class="d-flex align-items-start">
                    {{-- Icon --}}
                    <div class="notification-icon bg-{{ $color }} me-3">
                        <i class="fas {{ $icon }} text-{{ $color }}"></i>
                    </div>
                    
                    {{-- Content --}}
                    <div class="flex-grow-1">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h6 class="mb-1 fw-semibold text-{{ $color }}">
                                    {{ $title }}
                                    @if($isUnread)
                                        <span class="badge bg-primary ms-1" style="font-size: 0.65rem;">@lang('notifications.new')</span>
                                    @endif
                                </h6>
                                <p class="mb-1 text-muted">{{ $message }}</p>
                                <small class="text-muted">
                                    <i class="fas fa-clock me-1"></i>{{ $notification->created_at->diffForHumans() }}
                                </small>
                            </div>
                            
                            {{-- Actions --}}
                            <div class="d-flex align-items-center">
                                @if($link && $link !== '#')
                                    <a href="{{ $link }}" class="btn btn-sm btn-outline-{{ $color }} me-2">
                                        <i class="fas fa-external-link-alt me-1"></i>@lang('notifications.view')
                                    </a>
                                @endif
                                
                                @if($isUnread)
                                    <form action="{{ route('client.notifications.markAsRead', $notification->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-secondary me-2" title="@lang('notifications.mark_read')">
                                            <i class="fas fa-check"></i>
                                        </button>
                                    </form>
                                @endif
                                
                                <form action="{{ route('client.notifications.destroy', $notification->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="@lang('notifications.delete')" onclick="return confirm('@lang('notifications.confirm_delete')')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="fas fa-bell-slash fa-4x text-muted mb-3"></i>
                <h5 class="text-muted">@lang('notifications.no_notifications')</h5>
                <p class="text-muted mb-0">@lang('notifications.no_notifications_desc')</p>
            </div>
        </div>
    @endforelse

    {{-- Pagination --}}
    <div class="d-flex justify-content-center mt-4">
        {{ $notifications->links() }}
    </div>
</div>
@endsection
