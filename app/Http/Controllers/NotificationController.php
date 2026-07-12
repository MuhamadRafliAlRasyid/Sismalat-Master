<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        $notifications = $user->notifications()
            ->whereIn('type', [
                'App\Notifications\PeminjamanWarningNotification',
                'App\Notifications\AlatExpiredNotification',
            ])
            ->latest()
            ->take(10)
            ->get()
            ->map(function ($notif) {
                $data = $notif->data;
                return [
                    'id'           => $notif->id,
                    'type'         => $data['type'] ?? 'unknown',
                    'message'      => $data['message'] ?? '',
                    'icon'         => $data['icon'] ?? 'fa-bell',
                    'color'        => $data['color'] ?? 'gray',
                    'priority'     => $data['priority'] ?? 'normal',
                    'action_url'   => $data['action_url'] ?? '#',
                    'action_label' => $data['action_label'] ?? 'Lihat Detail', // ✨ BARU
                    'nama_alat'    => $data['nama_alat'] ?? '',
                    'created_at'   => $notif->created_at->diffForHumans(),
                    'read_at'      => $notif->read_at,
                ];
            });

        return response()->json([
            'success'       => true,
            'notifications' => $notifications,
            'unread_count'  => $user->unreadNotifications->count(),
        ]);
    }

    public function markAsRead($id)
    {
        $notification = Auth::user()->notifications()->findOrFail($id);
        $notification->markAsRead();

        return response()->json(['success' => true]);
    }

    public function markAllAsRead()
    {
        Auth::user()->unreadNotifications->markAsRead();

        return response()->json(['success' => true]);
    }
}
