<?php

namespace App\Http\Controllers\Api;

use App\Models\Pengambilan;
use App\Models\Spareparts;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;

class PengambilanSparepartController extends Controller
{
    public function index(Request $request)
    {
        try {
            $user = Auth::user();

            $query = Pengambilan::with(['user', 'bagian', 'sparepart']);

            // 🔥 FILTER USER
            if (!$user || $user->role !== 'admin') {
                $query->where('user_id', Auth::id());
            }

            $data = $query->latest()->paginate(15);

            return response()->json([
                'status' => true,
                'data' => $data->items(),
                'meta' => [
                    'current_page' => $data->currentPage(),
                    'last_page' => $data->lastPage(),
                    'total' => $data->total(),
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Pengambilan Index Error: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'Gagal mengambil data pengambilan'
            ], 500);
        }
    }

    public function store(Request $request)
{
    try {
        $request->validate([
            'spareparts_id'     => 'required|string',           // ← ubah ke string (hashid)
            'jumlah'            => 'required|integer|min:1',
            'satuan'            => 'required|string|max:50',
            'keperluan'         => 'required|string|max:255',
            'waktu_pengambilan' => 'required|date',
            'part_type'         => 'required|in:baru,bekas',
        ]);

        // Decode hashid menjadi ID asli
        $sparepartId = app(\App\Services\HashIdService::class)->decode($request->spareparts_id);

        if (!$sparepartId) {
            return response()->json([
                'status' => false,
                'message' => 'Hash ID sparepart tidak valid'
            ], 422);
        }

        $sparepart = Spareparts::findOrFail($sparepartId);

        // Cek stok
        if ($request->part_type === 'baru' && $sparepart->jumlah_baru < $request->jumlah) {
            return response()->json(['status' => false, 'message' => 'Stok baru tidak mencukupi'], 422);
        }
        if ($request->part_type === 'bekas' && $sparepart->jumlah_bekas < $request->jumlah) {
            return response()->json(['status' => false, 'message' => 'Stok bekas tidak mencukupi'], 422);
        }

        $pengambilan = Pengambilan::create([
            'user_id'           => Auth::id(),
            'bagian_id'         => Auth::user()->bagian_id ?? 1,
            'spareparts_id'     => $sparepartId,           // ← simpan ID integer
            'jumlah'            => $request->jumlah,
            'satuan'            => $request->satuan,
            'keperluan'         => $request->keperluan,
            'waktu_pengambilan' => $request->waktu_pengambilan,
            'part_type'         => $request->part_type,
        ]);

        // Kurangi stok
        if ($request->part_type === 'baru') {
            $sparepart->decrement('jumlah_baru', $request->jumlah);
        } else {
            $sparepart->decrement('jumlah_bekas', $request->jumlah);
        }

        return response()->json([
            'status' => true,
            'message' => 'Pengambilan sparepart berhasil dicatat',
            'data' => $pengambilan->load('sparepart')
        ], 201);

    } catch (\Illuminate\Validation\ValidationException $e) {
        return response()->json([
            'status' => false,
            'message' => 'Validasi gagal',
            'errors' => $e->errors()
        ], 422);
    } catch (\Exception $e) {
        Log::error('Pengambilan Store Error: ' . $e->getMessage());
        return response()->json([
            'status' => false,
            'message' => 'Gagal menyimpan pengambilan: ' . $e->getMessage()
        ], 500);
    }
}

   public function show(Request $request, $hashid)
    {
        try {
            Log::info('Pengambilan show accessed with hashid: ' . $hashid);

            $id = app(\App\Services\HashIdService::class)->decode($hashid);

            if ($id === null) {
                return response()->json([
                    'status' => false,
                    'message' => 'Hash ID tidak valid'
                ], 404);
            }

            $pengambilan = Pengambilan::with(['user', 'bagian', 'sparepart'])->find($id);

            if (!$pengambilan) {
                return response()->json([
                    'status' => false,
                    'message' => 'Data pengambilan tidak ditemukan'
                ], 404);
            }

            return response()->json([
                'status' => true,
                'data' => $pengambilan
            ]);

        } catch (\Exception $e) {
            Log::error('Pengambilan Show Error: ' . $e->getMessage() . ' | Hashid: ' . $hashid);
            return response()->json([
                'status' => false,
                'message' => 'Gagal mengambil detail pengambilan'
            ], 500);
        }
    }

    public function update(Request $request, $hashid)
{
    try {
        $request->validate([
            'spareparts_id'     => 'sometimes|required|string',
            'jumlah'            => 'sometimes|integer|min:1',
            'satuan'            => 'sometimes|string|max:50',
            'keperluan'         => 'sometimes|string|max:255',
            'waktu_pengambilan' => 'sometimes|date',
            'part_type'         => 'sometimes|required|in:baru,bekas',
        ]);

        $pengambilanId = app(\App\Services\HashIdService::class)->decode($hashid);
        if (!$pengambilanId) {
            return response()->json([
                'status' => false,
                'message' => 'Hash ID pengambilan tidak valid'
            ], 404);
        }

        $pengambilan = Pengambilan::findOrFail($pengambilanId);

        if (Auth::user()->role !== 'admin' && Auth::id() !== $pengambilan->user_id) {
            return response()->json(['status' => false, 'message' => 'Tidak memiliki izin'], 403);
        }

        $sparepartId = $pengambilan->spareparts_id;
        if ($request->has('spareparts_id') && $request->spareparts_id) {
            $sparepartId = app(\App\Services\HashIdService::class)->decode($request->spareparts_id);
            if (!$sparepartId) {
                return response()->json(['status' => false, 'message' => 'Hash ID sparepart tidak valid'], 422);
            }
        }

        $sparepart = Spareparts::findOrFail($sparepartId);

        // Kembalikan stok lama
        if ($pengambilan->part_type === 'baru') {
            $sparepart->increment('jumlah_baru', $pengambilan->jumlah);
        } else {
            $sparepart->increment('jumlah_bekas', $pengambilan->jumlah);
        }

        $newJumlah = $request->input('jumlah', $pengambilan->jumlah);
        $newPartType = $request->input('part_type', $pengambilan->part_type);

        // Cek stok baru
        if ($newPartType === 'baru' && $sparepart->jumlah_baru < $newJumlah) {
            return response()->json(['status' => false, 'message' => 'Stok baru tidak mencukupi'], 422);
        }
        if ($newPartType === 'bekas' && $sparepart->jumlah_bekas < $newJumlah) {
            return response()->json(['status' => false, 'message' => 'Stok bekas tidak mencukupi'], 422);
        }

        $pengambilan->update([
            'spareparts_id'     => $sparepartId,
            'jumlah'            => $newJumlah,
            'satuan'            => $request->input('satuan', $pengambilan->satuan),
            'keperluan'         => $request->input('keperluan', $pengambilan->keperluan),
            'waktu_pengambilan' => $request->input('waktu_pengambilan', $pengambilan->waktu_pengambilan),
            'part_type'         => $newPartType,
        ]);

        // Kurangi stok baru
        if ($newPartType === 'baru') {
            $sparepart->decrement('jumlah_baru', $newJumlah);
        } else {
            $sparepart->decrement('jumlah_bekas', $newJumlah);
        }

        return response()->json([
            'status' => true,
            'message' => 'Pengambilan berhasil diperbarui',
            'data' => $pengambilan->fresh()->load('sparepart')
        ]);

    } catch (\Illuminate\Validation\ValidationException $e) {
        return response()->json([
            'status' => false,
            'message' => 'Validasi gagal',
            'errors' => $e->errors()
        ], 422);
    } catch (\Exception $e) {
        Log::error('Pengambilan Update Error: ' . $e->getMessage());
        return response()->json([
            'status' => false,
            'message' => 'Gagal memperbarui pengambilan'
        ], 500);
    }
}

    public function destroy(Pengambilan $pengambilan)
    {
        try {
            $pengambilan->delete();
            return response()->json([
                'status' => true,
                'message' => 'Pengambilan berhasil dihapus'
            ]);
        } catch (\Exception $e) {
            Log::error('Pengambilan Destroy Error: ' . $e->getMessage());
            return response()->json(['status' => false, 'message' => 'Gagal menghapus data'], 500);
        }
    }
}
