<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function index()
    {
        $notifications = Auth::user()
            ->notifications()
            ->latest()
            ->paginate(20);

        return view('clients.notifications.index', compact('notifications'));
    }

    public function getLatest()
    {
        $user = Auth::user();
        
        $notifications = $user->notifications()
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($notification) {
                return [
                    'id'         => $notification->id,
                    'type'       => $notification->data['type'] ?? 'info',
                    'icon'       => $notification->data['icon'] ?? 'fa-bell',
                    'color'      => $notification->data['color'] ?? 'primary',
                    'title'      => $notification->data['title'] ?? '',
                    'message'    => $notification->data['message'] ?? '',
                    'link'       => $notification->data['link'] ?? '#',
                    'read'       => $notification->read_at !== null,
                    'created_at' => $notification->created_at->diffForHumans(),
                ];
            });

        $unreadCount = $user->unreadNotifications()->count();

        return response()->json([
            'notifications' => $notifications,
            'unread_count'  => $unreadCount,
        ]);
    }

    public function markAsRead(Request $request, $id)
    {
        $notification = Auth::user()
            ->notifications()
            ->where('id', $id)
            ->first();

        if ($notification) {
            $notification->markAsRead();
        }

        if ($request->ajax()) {
            return response()->json(['success' => true]);
        }

        return back();
    }

    public function markAllAsRead(Request $request)
    {
        Auth::user()->unreadNotifications->markAsRead();

        if ($request->ajax()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', __('notifications.all_marked_read'));
    }

    public function destroy($id)
    {
        $notification = Auth::user()
            ->notifications()
            ->where('id', $id)
            ->first();

        if ($notification) {
            $notification->delete();
        }

        return back()->with('success', __('notifications.deleted'));
    }

    public function getNewNotifications(Request $request)
    {
        $lastCheck = $request->input('last_check');
        
        $query = Auth::user()->notifications();
        
        if ($lastCheck) {
            $query->where('created_at', '>', $lastCheck);
        }

        $newNotifications = $query->latest()
            ->take(5)
            ->get()
            ->map(function ($notification) {
                return [
                    'id'         => $notification->id,
                    'type'       => $notification->data['type'] ?? 'info',
                    'icon'       => $notification->data['icon'] ?? 'fa-bell',
                    'color'      => $notification->data['color'] ?? 'primary',
                    'title'      => $notification->data['title'] ?? '',
                    'message'    => $notification->data['message'] ?? '',
                    'link'       => $notification->data['link'] ?? '#',
                    'created_at' => $notification->created_at->diffForHumans(),
                ];
            });

        return response()->json([
            'notifications' => $newNotifications,
            'server_time'   => now()->toIso8601String(),
        ]);
    }
}
