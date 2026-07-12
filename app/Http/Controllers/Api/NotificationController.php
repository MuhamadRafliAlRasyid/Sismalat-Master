<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class NotificationController extends Controller
{
    /**
     * ✅ GET /api/notifications
     * Ambil semua notifikasi user yang sedang login
     */
    public function index(Request $request)
    {
        try {
            $user = Auth::user();

            // ✅ Validasi user
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated',
                    'notifications' => [],
                    'unread_count' => 0,
                ], 401);
            }

            Log::info('🔔 [Api\NotificationController@index] Request from user: ' . $user->id);

            // ✅ Ambil notifikasi (max 50 terbaru)
            $notifications = $user->notifications()
                ->latest()
                ->take(50)
                ->get()
                ->map(function ($notif) {
                    $data = $notif->data;

                    // ✅ Handle data jika string (JSON)
                    if (is_string($data)) {
                        $data = json_decode($data, true) ?? [];
                    }

                    return [
                        'id'              => $notif->id,
                        'type'            => $data['type'] ?? class_basename($notif->type),
                        'message'         => $data['message'] ?? '',
                        'icon'            => $data['icon'] ?? 'bell',
                        'color'           => $data['color'] ?? 'gray',
                        'priority'        => $data['priority'] ?? 'normal',
                        'action_url'      => $data['action_url'] ?? '#',
                        'action_label'    => $data['action_label'] ?? 'Lihat Detail',
                        'nama_alat'       => $data['nama_alat'] ?? '',
                        'alat_id'         => $data['alat_id'] ?? null,
                        'alat_hashid'     => $data['alat_hashid'] ?? null,
                        'pengambilan_id'  => $data['pengambilan_id'] ?? null,
                        'hashid'          => $data['hashid'] ?? null,
                        'sisa_hari'       => $data['sisa_hari'] ?? null,
                        'persentase_sisa' => $data['persentase_sisa'] ?? null,
                        'jatuh_tempo'     => $data['jatuh_tempo'] ?? null,
                        'created_at'      => $notif->created_at->toIso8601String(),
                        'created_human'   => $notif->created_at->diffForHumans(),
                        'read_at'         => $notif->read_at,
                        'read'            => !is_null($notif->read_at),
                    ];
                });

            // ✅ Hitung unread count
            $unreadCount = $user->unreadNotifications->count();

            Log::info('✅ [Api\NotificationController@index] Success', [
                'total' => $notifications->count(),
                'unread' => $unreadCount,
            ]);

            return response()->json([
                'success'       => true,
                'notifications' => $notifications,
                'unread_count'  => $unreadCount,
            ]);

        } catch (\Exception $e) {
            Log::error('❌ [Api\NotificationController@index] Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Server error: ' . $e->getMessage(),
                'notifications' => [],
                'unread_count' => 0,
            ], 500);
        }
    }

    /**
     * ✅ GET /api/notifications/unread-count
     */
    public function unreadCount()
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'count' => 0,
                ], 401);
            }

            $count = $user->unreadNotifications->count();

            return response()->json([
                'success' => true,
                'count' => $count,
            ]);
        } catch (\Exception $e) {
            Log::error('❌ [Api\NotificationController@unreadCount] Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'count' => 0,
            ], 500);
        }
    }

    /**
     * ✅ POST /api/notifications/{id}/mark-read
     */
    public function markAsRead($id)
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated'
                ], 401);
            }

            $notification = $user->notifications()->find($id);

            if ($notification) {
                $notification->markAsRead();

                return response()->json([
                    'success' => true,
                    'message' => 'Notifikasi ditandai sudah dibaca',
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Notifikasi tidak ditemukan'
            ], 404);
        } catch (\Exception $e) {
            Log::error('❌ [Api\NotificationController@markAsRead] Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal menandai notifikasi: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * ✅ POST /api/notifications/mark-all-read
     */
    public function markAllAsRead()
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated'
                ], 401);
            }

            $user->unreadNotifications->markAsRead();

            return response()->json([
                'success' => true,
                'message' => 'Semua notifikasi ditandai sudah dibaca',
            ]);
        } catch (\Exception $e) {
            Log::error('❌ [Api\NotificationController@markAllAsRead] Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal menandai semua notifikasi: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * ✅ DELETE /api/notifications/{id}
     */
    public function destroy($id)
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated'
                ], 401);
            }

            $notification = $user->notifications()->find($id);

            if ($notification) {
                $notification->delete();

                return response()->json([
                    'success' => true,
                    'message' => 'Notifikasi dihapus',
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Notifikasi tidak ditemukan'
            ], 404);
        } catch (\Exception $e) {
            Log::error('❌ [Api\NotificationController@destroy] Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus notifikasi: ' . $e->getMessage()
            ], 500);
        }
    }
}
