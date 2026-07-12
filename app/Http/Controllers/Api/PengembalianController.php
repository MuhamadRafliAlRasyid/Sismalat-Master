<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pengambilan;
use App\Models\Pengembalian;
use App\Models\Spareparts;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PengembalianController extends Controller
{
    // ==================== INDEX ====================
    public function index(Request $request)
    {
        try {
            $user = Auth::user();

            $query = Pengembalian::with([
                'sparepart',
                'user',
                'bagian',
                'pengambilan',
                'pengambilan.sparepart',
                'pengambilan.user',
            ])->latest();

            // FILTER USER
            if (!$user || $user->role !== 'admin') {
                $query->where('user_id', Auth::id());
            }

            // SEARCH
            if ($request->filled('search')) {
                $search = $request->search;

                $query->whereHas('sparepart', function ($q) use ($search) {
                    $q->where('nama_part', 'like', "%{$search}%");
                });
            }

            $data = $query->paginate(15);

            return response()->json([
                'status' => true,
                'data'   => $data->items(),
                'meta'   => [
                    'current_page' => $data->currentPage(),
                    'last_page'    => $data->lastPage(),
                    'total'        => $data->total(),
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('INDEX ERROR PENGEMBALIAN', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'status' => false,
                'message' => 'Gagal mengambil data'
            ], 500);
        }
    }

    // ==================== STORE ====================
    public function store(Request $request)
    {
        DB::beginTransaction();

        try {
            Log::info('REQUEST PENGEMBALIAN', $request->all());

            $request->validate([
                'pengambilan_hashid' => 'required',
                'jumlah_dikembalikan' => 'required|numeric|min:1',
                'kondisi' => 'required|in:baik,rusak',
                'alasan' => 'nullable|string'
            ]);

            // ================= DECODE =================
            $pengambilanId = app(\App\Services\HashIdService::class)
                ->decode($request->pengambilan_hashid);

            if (!$pengambilanId && is_numeric($request->pengambilan_hashid)) {
                $pengambilanId = (int) $request->pengambilan_hashid;
            }

            if (!$pengambilanId) {
                throw new \Exception('Hashid tidak valid');
            }

            $pengambilan = Pengambilan::findOrFail($pengambilanId);

            // ================= VALIDASI =================
            $sisa = $pengambilan->jumlah - $pengambilan->jumlah_dikembalikan;

            if ($request->jumlah_dikembalikan > $sisa) {
                return response()->json([
                    'status' => false,
                    'message' => "Maksimal pengembalian: $sisa"
                ], 400);
            }

            $sparepart = Spareparts::findOrFail($pengambilan->spareparts_id);

            // ================= SIMPAN =================
            $pengembalian = Pengembalian::create([
                'pengambilan_id' => $pengambilan->id,
                'sparepart_id'   => $sparepart->id,
                'user_id'        => Auth::id(),
                'bagian_id'      => Auth::user()->bagian_id,
                'jumlah_dikembalikan' => $request->jumlah_dikembalikan,
                'kondisi'        => $request->kondisi,
                'alasan'         => $request->alasan,
                'keterangan'     => $request->keterangan,
                'tanggal_kembali'=> now(),
            ]);

            // ================= UPDATE PENGAMBILAN =================
            $pengambilan->increment(
                'jumlah_dikembalikan',
                $request->jumlah_dikembalikan
            );

            // ================= UPDATE STOK =================
            if ($request->kondisi === 'baik') {
                $sparepart->increment('jumlah_baru', $request->jumlah_dikembalikan);
            } else {
                $sparepart->increment('jumlah_bekas', $request->jumlah_dikembalikan);
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Pengembalian berhasil',
                'data' => $pengembalian->load([
                    'sparepart',
                    'user'
                ])
            ]);

        } catch (\Throwable $e) {
            DB::rollBack();

            Log::error('STORE ERROR PENGEMBALIAN', [
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'request' => $request->all()
            ]);

            return response()->json([
                'status' => false,
                'message' => 'Gagal menyimpan',
                'debug' => $e->getMessage()
            ], 500);
        }
    }

    // ==================== SHOW ====================
    public function show($hashid)
    {
        try {
            // ================= DECODE =================
            $id = app(\App\Services\HashIdService::class)
                ->decode($hashid);

            if ($id === null && is_numeric($hashid)) {
                $id = (int) $hashid;
            }

            if (!$id) {
                return response()->json([
                    'status' => false,
                    'message' => 'Hashid tidak valid'
                ], 404);
            }

            $pengembalian = Pengembalian::with([
                'user',
                'bagian',
                'sparepart',
                'pengambilan',
                'pengambilan.sparepart',
                'pengambilan.user',
            ])->find($id);

            if (!$pengembalian) {
                return response()->json([
                    'status' => false,
                    'message' => 'Data tidak ditemukan'
                ], 404);
            }

            // ================= TAMBAH HASHID RELASI =================
            if ($pengembalian->pengambilan) {
                $pengembalian->pengambilan->hashid =
                    app(\App\Services\HashIdService::class)
                        ->encode($pengembalian->pengambilan->id);
            }

            return response()->json([
                'status' => true,
                'data' => $pengembalian
            ]);

        } catch (\Exception $e) {
            Log::error('SHOW ERROR', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'status' => false,
                'message' => 'Gagal mengambil data'
            ], 500);
        }
    }

    // ==================== UPDATE ====================
    public function update(Request $request, $hashid)
    {
        DB::beginTransaction();

        try {
            // decode
            $id = app(\App\Services\HashIdService::class)
                ->decode($hashid);

            if ($id === null && is_numeric($hashid)) {
                $id = (int) $hashid;
            }

            $pengembalian = Pengembalian::findOrFail($id);

            $pengembalian->update([
                'jumlah_dikembalikan' => $request->jumlah_dikembalikan,
                'kondisi' => $request->kondisi,
                'alasan' => $request->alasan,
                'keterangan' => $request->keterangan,
            ]);

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Berhasil update'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => 'Gagal update'
            ], 500);
        }
    }

    // ==================== DELETE ====================
    public function destroy($hashid)
    {
        DB::beginTransaction();

        try {
            $id = app(\App\Services\HashIdService::class)
                ->decode($hashid);

            if ($id === null && is_numeric($hashid)) {
                $id = (int) $hashid;
            }

            $pengembalian = Pengembalian::findOrFail($id);

            $pengambilan = $pengembalian->pengambilan;
            $sparepart = $pengembalian->sparepart;

            // rollback stok
            if ($pengembalian->kondisi === 'baik') {
                $sparepart->decrement('jumlah_baru', $pengembalian->jumlah_dikembalikan);
            } else {
                $sparepart->decrement('jumlah_bekas', $pengembalian->jumlah_dikembalikan);
            }

            // rollback pengambilan
            $pengambilan->decrement(
                'jumlah_dikembalikan',
                $pengembalian->jumlah_dikembalikan
            );

            $pengembalian->delete();

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Berhasil dihapus'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => 'Gagal hapus'
            ], 500);
        }
    }
}
