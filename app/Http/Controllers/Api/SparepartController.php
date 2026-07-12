<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Spareparts;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class SparepartController extends Controller
{
    private function resolveId($identifier)
{
    // Coba decode sebagai hashid
    $decoded = app(\App\Services\HashIdService::class)->decode($identifier);

    if ($decoded !== null) {
        return $decoded;
    }

    // fallback: kalau numeric ID
    if (is_numeric($identifier)) {
        return (int) $identifier;
    }

    return null;
}
    // ==================== INDEX (List Aktif) ====================
    public function index(Request $request)
    {
        try {
            $query = Spareparts::query();

            if ($request->filled('search')) {
                $search = $request->input('search');
                $query->where(function ($q) use ($search) {
                    $q->where('nama_part', 'like', "%{$search}%")
                      ->orWhere('model', 'like', "%{$search}%")
                      ->orWhere('merk', 'like', "%{$search}%")
                      ->orWhere('ruk_no', 'like', "%{$search}%");
                });
            }

            $spareparts = $query->latest()->paginate(15);

            return response()->json([
                'status' => true,
                'data'   => $spareparts->items(),
                'meta'   => [
                    'current_page' => $spareparts->currentPage(),
                    'last_page'    => $spareparts->lastPage(),
                    'total'        => $spareparts->total(),
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Sparepart Index Error: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'Gagal mengambil data sparepart'
            ], 500);
        }
    }

    // ==================== SHOW DETAIL ====================
    public function show(Request $request, $identifier)
{
    try {
        Log::info('Sparepart show accessed with identifier: ' . $identifier);

        $id = $this->resolveId($identifier);

        if ($id === null) {
            return response()->json([
                'status' => false,
                'message' => 'ID tidak valid'
            ], 404);
        }

        $sparepart = Spareparts::find($id);

        if (!$sparepart) {
            return response()->json([
                'status' => false,
                'message' => 'Sparepart tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'status' => true,
            'data' => [
                ...$sparepart->toArray(),
                'hashid' => app(\App\Services\HashIdService::class)->encode($sparepart->id),
            ]
        ]);

    } catch (\Exception $e) {
        Log::error('Sparepart Show Error: ' . $e->getMessage() . ' | identifier: ' . $identifier);

        return response()->json([
            'status' => false,
            'message' => 'Gagal mengambil detail sparepart'
        ], 500);
    }
}


    // ==================== STORE ====================
    public function store(Request $request)
    {
        try {
            $request->validate([
                'nama_part'     => 'required|string|max:255',
                'model'         => 'required|string|max:255',
                'merk'          => 'required|string|max:255',
                'jumlah_baru'   => 'required|numeric|min:0',
                'jumlah_bekas'  => 'required|numeric|min:0',
                'supplier'      => 'required|string|max:255',
                'patokan_harga' => 'required|numeric',
                'ruk_no'        => 'required|string|max:100',
                'purchase_date' => 'required|date',
                'delivery_date' => 'required|date',
            ]);

            $sparepart = Spareparts::create($request->all());

            return response()->json([
                'status'  => true,
                'message' => 'Sparepart berhasil ditambahkan',
                'data'    => $sparepart
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Validasi gagal',
                'errors'  => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Sparepart Store Error: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'Gagal menyimpan sparepart'
            ], 500);
        }
    }

    // ==================== UPDATE ====================
    public function update(Request $request, $hashid)
    {
        try {
            Log::info('Sparepart update accessed with hashid: ' . $hashid);

            $id = app(\App\Services\HashIdService::class)->decode($hashid);

            if ($id === null) {
                return response()->json([
                    'status' => false,
                    'message' => 'Hash ID tidak valid'
                ], 404);
            }

            $sparepart = Spareparts::find($id);

            if (!$sparepart) {
                return response()->json([
                    'status' => false,
                    'message' => 'Sparepart tidak ditemukan'
                ], 404);
            }

            $request->validate([
                'nama_part'     => 'required|string|max:255',
                'model'         => 'required|string|max:255',
                'merk'          => 'required|string|max:255',
                'jumlah_baru'   => 'required|numeric|min:0',
                'jumlah_bekas'  => 'required|numeric|min:0',
                'supplier'      => 'required|string|max:255',
                'patokan_harga' => 'required|numeric',
                'ruk_no'        => 'required|string|max:100',
            ]);

            $sparepart->update($request->all());

            return response()->json([
                'status'  => true,
                'message' => 'Sparepart berhasil diperbarui',
                'data'    => $sparepart
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Validasi gagal',
                'errors'  => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Sparepart Update Error: ' . $e->getMessage() . ' | Hashid: ' . $hashid);
            return response()->json([
                'status' => false,
                'message' => 'Gagal memperbarui sparepart'
            ], 500);
        }
    }

    // ==================== SOFT DELETE ====================
    public function destroy(Spareparts $sparepart)
    {
        try {
            $sparepart->delete();
            return response()->json([
                'status' => true,
                'message' => 'Sparepart berhasil dihapus (soft delete)'
            ]);
        } catch (\Exception $e) {
            Log::error('Sparepart Destroy Error: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'Gagal menghapus sparepart'
            ], 500);
        }
    }

       // ==================== TRASHED (Daftar Barang Terhapus) ====================
    public function trashed()
    {
        try {
            $trashed = Spareparts::onlyTrashed()
                ->latest()
                ->paginate(15);

            return response()->json([
                'status' => true,
                'data'   => $trashed->items(),
                'meta'   => [
                    'current_page' => $trashed->currentPage(),
                    'last_page'    => $trashed->lastPage(),
                    'total'        => $trashed->total(),
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Sparepart Trashed Error: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'Gagal mengambil data sparepart terhapus'
            ], 500);
        }
    }

    // ==================== RESTORE ====================
    public function restore($hashid)
    {
        try {
            $id = app(\App\Services\HashIdService::class)->decode($hashid);

            if ($id === null) {
                return response()->json([
                    'status' => false,
                    'message' => 'Hash ID tidak valid'
                ], 404);
            }

            $sparepart = Spareparts::onlyTrashed()->find($id);

            if (!$sparepart) {
                return response()->json([
                    'status' => false,
                    'message' => 'Sparepart tidak ditemukan di trash'
                ], 404);
            }

            $sparepart->restore();

            return response()->json([
                'status' => true,
                'message' => 'Sparepart berhasil dikembalikan dari trash'
            ]);
        } catch (\Exception $e) {
            Log::error('Sparepart Restore Error: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'Gagal mengembalikan sparepart'
            ], 500);
        }
    }

    // ==================== FORCE DELETE (Hapus Permanen) ====================
    public function forceDelete($hashid)
    {
        try {
            $id = app(\App\Services\HashIdService::class)->decode($hashid);

            if ($id === null) {
                return response()->json([
                    'status' => false,
                    'message' => 'Hash ID tidak valid'
                ], 404);
            }

            $sparepart = Spareparts::onlyTrashed()->find($id);

            if (!$sparepart) {
                return response()->json([
                    'status' => false,
                    'message' => 'Sparepart tidak ditemukan'
                ], 404);
            }

            $sparepart->forceDelete();

            return response()->json([
                'status' => true,
                'message' => 'Sparepart berhasil dihapus secara permanen'
            ]);
        } catch (\Exception $e) {
            Log::error('Sparepart Force Delete Error: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'Gagal menghapus permanen sparepart'
            ], 500);
        }
    }
    // ==================== CHECK STOCK ====================
    public function checkStock()
    {
        try {
            $critical = Spareparts::whereColumn('jumlah_baru', '<=', 'titik_pesanan')->count();

            return response()->json([
                'status' => true,
                'critical_count' => $critical,
                'message' => $critical > 0 ? 'Ada sparepart kritis' : 'Stok aman'
            ]);
        } catch (\Exception $e) {
            Log::error('Check Stock Error: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'Gagal memeriksa stok'
            ], 500);
        }
    }

    public function regenerateQrCode(Spareparts $sparepart)
    {
        try {
            return response()->json([
                'status'  => true,
                'message' => 'QR Code berhasil diregenerate untuk ' . $sparepart->nama_part,
                'data'    => $sparepart
            ]);
        } catch (\Exception $e) {
            Log::error('Regenerate QR Error: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'Gagal meregenerate QR Code'
            ], 500);
        }
    }
    // Di dalam class SparepartController

public function showByNumericId($id)
{
    try {
        Log::info('Sparepart showByNumericId called with id: ' . $id);

        if (!is_numeric($id)) {
            return response()->json([
                'status' => false,
                'message' => 'ID harus berupa angka'
            ], 422);
        }

        $sparepart = Spareparts::find($id);

        if (!$sparepart) {
            return response()->json([
                'status' => false,
                'message' => 'Sparepart tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'status' => true,
            'data' => array_merge($sparepart->toArray(), [
                'hashid' => $sparepart->hashid,   // pastikan trait HasHashId aktif
            ])
        ]);

    } catch (\Exception $e) {
        Log::error('Sparepart showByNumericId Error: ' . $e->getMessage());
        return response()->json([
            'status' => false,
            'message' => 'Gagal mengambil data sparepart'
        ], 500);
    }
}
}
