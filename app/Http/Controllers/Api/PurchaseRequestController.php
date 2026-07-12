<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PurchaseRequest;
use App\Services\HashIdService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PurchaseRequestController extends Controller
{
    // Helper untuk decode hashid
    protected function resolveHashid($hashid)
    {
        $id = app(HashIdService::class)->decode($hashid);
        if (!$id) {
            abort(404, 'Hash ID tidak valid');
        }
        return $id;
    }

    // ==================== INDEX ====================
    public function index(Request $request)
    {
        try {
            $query = PurchaseRequest::with('user');

            if (!in_array(Auth::user()->role, ['super', 'admin'])) {
                $query->where('user_id', Auth::id());
            }

            $data = $query->latest()->paginate(15);

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
            Log::error('PurchaseRequest Index Error: ' . $e->getMessage());
            return response()->json([
                'status'  => false,
                'message' => 'Gagal mengambil data purchase request'
            ], 500);
        }
    }

    // ==================== STORE ====================
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'nama_part'           => 'required|string|max:255',
                'part_number'         => 'required|string|max:255',
                'link_website'        => 'nullable|url',
                'waktu_request'       => 'required|date',
                'quantity'            => 'required|integer|min:1',
                'satuan'              => 'required|string|max:50',
                'mas_deliver'         => 'required|date|after_or_equal:waktu_request',
                'untuk_apa'           => 'required|string|max:500',
                'pic'                 => 'required|string|max:255',
                'quotation_lead_time' => 'nullable|string|max:255',
                'sparepart_id'        => 'nullable|string',
            ]);

            $sparepartId = null;
            if (!empty($request->sparepart_id)) {
                $sparepartId = app(HashIdService::class)->decode($request->sparepart_id);
            }

            $pr = PurchaseRequest::create([
                'user_id'             => Auth::id(),
                'nama_part'           => $validated['nama_part'],
                'part_number'         => $validated['part_number'],
                'link_website'        => $validated['link_website'] ?? null,
                'waktu_request'       => $validated['waktu_request'],
                'quantity'            => $validated['quantity'],
                'satuan'              => $validated['satuan'],
                'mas_deliver'         => $validated['mas_deliver'],
                'untuk_apa'           => $validated['untuk_apa'],
                'pic'                 => $validated['pic'],
                'quotation_lead_time' => $validated['quotation_lead_time'] ?? null,
                'sparepart_id'        => $sparepartId,
                'status'              => 'PR',
            ]);

            return response()->json([
                'status'  => true,
                'message' => 'Purchase Request berhasil dibuat',
                'data'    => $pr
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation Error in Store: ', $e->errors());
            return response()->json([
                'status'  => false,
                'message' => 'Validasi gagal',
                'errors'  => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('PurchaseRequest Store Error: ' . $e->getMessage());
            return response()->json([
                'status'  => false,
                'message' => 'Gagal membuat purchase request'
            ], 500);
        }
    }

    // ==================== SHOW ====================
    public function show($hashid)
    {
        try {
            $id = $this->resolveHashid($hashid);
            $purchaseRequest = PurchaseRequest::with('user')->findOrFail($id);

            return response()->json([
                'status' => true,
                'data'   => $purchaseRequest
            ]);
        } catch (\Exception $e) {
            Log::error('PurchaseRequest Show Error: ' . $e->getMessage() . ' | Hashid: ' . $hashid);
            return response()->json([
                'status'  => false,
                'message' => 'Gagal mengambil detail purchase request'
            ], 500);
        }
    }

    // ==================== UPDATE ====================
    public function update(Request $request, $hashid)
    {
        try {
            $id = $this->resolveHashid($hashid);
            $purchaseRequest = PurchaseRequest::findOrFail($id);

            $validated = $request->validate([
                'nama_part'           => 'required|string|max:255',
                'part_number'         => 'required|string|max:255',
                'link_website'        => 'nullable|url',
                'waktu_request'       => 'required|date',
                'quantity'            => 'required|integer|min:1',
                'satuan'              => 'required|string|max:50',
                'mas_deliver'         => 'required|date|after_or_equal:waktu_request',
                'untuk_apa'           => 'required|string|max:500',
                'pic'                 => 'required|string|max:255',
                'quotation_lead_time' => 'nullable|string|max:255',
                'sparepart_id'        => 'nullable|string',
            ]);

            $sparepartId = null;
            if (!empty($request->sparepart_id)) {
                $sparepartId = app(HashIdService::class)->decode($request->sparepart_id);
            }

            $purchaseRequest->update([
                'nama_part'           => $validated['nama_part'],
                'part_number'         => $validated['part_number'],
                'link_website'        => $validated['link_website'] ?? null,
                'waktu_request'       => $validated['waktu_request'],
                'quantity'            => $validated['quantity'],
                'satuan'              => $validated['satuan'],
                'mas_deliver'         => $validated['mas_deliver'],
                'untuk_apa'           => $validated['untuk_apa'],
                'pic'                 => $validated['pic'],
                'quotation_lead_time' => $validated['quotation_lead_time'] ?? null,
                'sparepart_id'        => $sparepartId,
            ]);

            return response()->json([
                'status'  => true,
                'message' => 'Purchase Request berhasil diperbarui',
                'data'    => $purchaseRequest->fresh()
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Validasi gagal',
                'errors'  => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('PurchaseRequest Update Error: ' . $e->getMessage());
            return response()->json([
                'status'  => false,
                'message' => 'Gagal memperbarui purchase request'
            ], 500);
        }
    }

    // ==================== DESTROY ====================
    public function destroy($hashid)
    {
        try {
            $id = $this->resolveHashid($hashid);
            $purchaseRequest = PurchaseRequest::findOrFail($id);
            $purchaseRequest->delete();

            return response()->json([
                'status'  => true,
                'message' => 'Purchase Request berhasil dihapus'
            ]);
        } catch (\Exception $e) {
            Log::error('PurchaseRequest Destroy Error: ' . $e->getMessage());
            return response()->json([
                'status'  => false,
                'message' => 'Gagal menghapus data'
            ], 500);
        }
    }

    // ==================== APPROVE & REJECT ====================
    public function approve($hashid)
    {
        try {
            $id = $this->resolveHashid($hashid);
            $purchaseRequest = PurchaseRequest::findOrFail($id);

            $purchaseRequest->update(['status' => 'PO']);

            return response()->json([
                'status'  => true,
                'message' => 'Purchase Request disetujui'
            ]);
        } catch (\Exception $e) {
            Log::error('Approve PR Error: ' . $e->getMessage());
            return response()->json(['status' => false, 'message' => 'Gagal menyetujui'], 500);
        }
    }

    public function reject(Request $request, $hashid)
    {
        try {
            $id = $this->resolveHashid($hashid);
            $purchaseRequest = PurchaseRequest::findOrFail($id);

            $request->validate(['notes' => 'required|string|max:255']);

            $purchaseRequest->update(['status' => 'Rejected']);

            return response()->json([
                'status'  => true,
                'message' => 'Purchase Request ditolak',
                'notes'   => $request->notes
            ]);
        } catch (\Exception $e) {
            Log::error('Reject PR Error: ' . $e->getMessage());
            return response()->json(['status' => false, 'message' => 'Gagal menolak request'], 500);
        }
    }
        // ==================== COMPLETE (PO → Selesai + Tambah Stok) ====================
    public function complete($hashid)
{
    try {
        $id = $this->resolveHashid($hashid);

        // Load dengan relasi sparepart
        $purchaseRequest = PurchaseRequest::with('sparepart')->findOrFail($id);

        if (!in_array(Auth::user()->role, ['admin', 'super'])) {
            return response()->json([
                'status' => false,
                'message' => 'Hanya admin/super yang dapat menyelesaikan PO'
            ], 403);
        }

        if ($purchaseRequest->status !== 'PO') {
            return response()->json([
                'status' => false,
                'message' => 'Hanya PO yang dapat diselesaikan'
            ], 422);
        }

        $quantity = (int) $purchaseRequest->quantity;
        $updated = false;
        $newStock = 0;

        // Tambah stok jika ada sparepart
        if ($purchaseRequest->sparepart_id && $purchaseRequest->sparepart) {
            $sparepart = $purchaseRequest->sparepart;

            $oldStock = (int) $sparepart->jumlah_baru;
            $sparepart->increment('jumlah_baru', $quantity);
            $newStock = (int) $sparepart->fresh()->jumlah_baru;

            // Reset relasi agar tidak muncul lagi di notifikasi
            $sparepart->update(['purchase_request_id' => null]);

            $updated = true;

            Log::info("Stok bertambah dari Complete PO: {$sparepart->nama_part} | {$oldStock} → {$newStock} (+{$quantity})");
        } else {
            Log::warning("Purchase Request ID {$id} tidak memiliki sparepart atau relasi gagal");
        }

        // Ubah status
        $purchaseRequest->update(['status' => 'Completed']);

        // Hapus notifikasi critical stock
        if ($purchaseRequest->sparepart_id) {
            DB::table('notifications')
                ->where('type', 'App\\Notifications\\SparepartCriticalNotification')
                ->whereJsonContains('data->sparepart_id', $purchaseRequest->sparepart_id)
                ->delete();
        }

        $message = $updated
            ? "PO berhasil diselesaikan! Stok bertambah {$quantity} unit (total: {$newStock})"
            : "PO berhasil diselesaikan (sparepart tidak ditemukan)";

        return response()->json([
            'status'  => true,
            'message' => $message,
            'data'    => $purchaseRequest->fresh()->load('sparepart')
        ]);

    } catch (\Exception $e) {
        Log::error('Complete PR Error: ' . $e->getMessage());
        return response()->json([
            'status'  => false,
            'message' => 'Gagal menyelesaikan PO'
        ], 500);
    }
}
}
