<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Alat;
use App\Models\PengambilanAlat;
use App\Models\PengembalianAlat;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class DashboardController extends Controller
{
    /**
     * ✅ GET /api/admin/dashboard/stats
     */
    public function adminStats()
    {
        try {
            $user = Auth::user();

            if (!$user || ($user->role !== 'admin' && $user->role !== 'super')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 403);
            }

            $totalAlat = Alat::count();
            $totalDipinjam = PengambilanAlat::where('status', 'dipinjam')->sum('jumlah') ?: 0;
            $totalDikembalikan = PengembalianAlat::sum('jumlah') ?: 0;

            Log::info('✅ [DashboardController@adminStats] Success', [
                'total_alat' => $totalAlat,
                'total_dipinjam' => $totalDipinjam,
                'total_dikembalikan' => $totalDikembalikan,
            ]);

            return response()->json([
                'success' => true,
                'total_alat' => $totalAlat,
                'total_dipinjam' => $totalDipinjam,
                'total_dikembalikan' => $totalDikembalikan,
            ]);
        } catch (\Exception $e) {
            Log::error('❌ [DashboardController@adminStats] Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data: ' . $e->getMessage(),
                'total_alat' => 0,
                'total_dipinjam' => 0,
                'total_dikembalikan' => 0,
            ], 500);
        }
    }

    /**
     * ✅ GET /api/karyawan/dashboard/stats
     */
    public function karyawanStats()
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 403);
            }

            $userId = $user->id;

            $totalDipinjam = PengambilanAlat::where('user_id', $userId)
                ->where('status', 'dipinjam')
                ->count();

            $totalDikembalikan = PengembalianAlat::where('user_id', $userId)->count();

            $alatTersedia = Alat::where('jumlah', '>', 0)->count();

            $pengambilanTerbaru = PengambilanAlat::with('alat')
                ->where('user_id', $userId)
                ->latest()
                ->take(5)
                ->get()
                ->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'hashid' => $item->hashid,
                        'nama_alat' => $item->alat->nama_alat ?? '-',
                        'status' => $item->status,
                        'created_at' => $item->created_at->toIso8601String(),
                    ];
                });

            $alatDipinjam = PengambilanAlat::with('alat')
                ->where('user_id', $userId)
                ->where('status', 'dipinjam')
                ->get()
                ->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'hashid' => $item->hashid,
                        'nama_alat' => $item->alat->nama_alat ?? '-',
                        'merk' => $item->alat->merk ?? '-',
                        'foto_thumb' => $item->alat->foto
                            ? url('/storage/alat/thumb_' . $item->alat->foto)
                            : null,
                        'jumlah' => $item->jumlah,
                        'satuan' => $item->satuan,
                        'waktu_pengambilan' => $item->waktu_pengambilan,
                    ];
                });

            Log::info('✅ [DashboardController@karyawanStats] Success', [
                'user_id' => $userId,
                'total_dipinjam' => $totalDipinjam,
                'total_dikembalikan' => $totalDikembalikan,
            ]);

            return response()->json([
                'success' => true,
                'total_dipinjam' => $totalDipinjam,
                'total_dikembalikan' => $totalDikembalikan,
                'alat_tersedia' => $alatTersedia,
                'pengambilan_terbaru' => $pengambilanTerbaru,
                'alat_dipinjam' => $alatDipinjam,
            ]);
        } catch (\Exception $e) {
            Log::error('❌ [DashboardController@karyawanStats] Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data: ' . $e->getMessage(),
                'total_dipinjam' => 0,
                'total_dikembalikan' => 0,
                'alat_tersedia' => 0,
                'pengambilan_terbaru' => [],
                'alat_dipinjam' => [],
            ], 500);
        }
    }
}
