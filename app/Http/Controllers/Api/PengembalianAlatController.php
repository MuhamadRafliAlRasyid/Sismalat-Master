<?php

namespace App\Http\Controllers\Api;

use App\Models\Alat;
use App\Models\PengambilanAlat;
use App\Models\PengembalianAlat;
use App\Services\HashIdService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\ModelNotFoundException;

// ✨ IMPORT UNTUK INTERVENTION IMAGE v4 (BUKAN Facade!)
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;

class PengembalianAlatController extends Controller
{
    protected function resolveHashid($hashid)
    {
        $id = app(HashIdService::class)->decode($hashid);
        if (!$id) {
            // Throw exception agar otomatis dihandle jadi JSON response 404 oleh Laravel
            throw new ModelNotFoundException('Data tidak ditemukan.');
        }
        return $id;
    }

    /* ================= INDEX ================= */
    public function index(Request $request)
    {
        $query = PengembalianAlat::with(['pengambilan.alat', 'user']);

        if (Auth::user()->role !== 'admin') {
            $query->where('user_id', Auth::id());
        }

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->whereHas('pengambilan.alat', function ($q2) use ($request) {
                    $q2->where('nama_alat', 'like', '%' . $request->search . '%');
                })
                ->orWhereHas('user', function ($q2) use ($request) {
                    $q2->where('name', 'like', '%' . $request->search . '%');
                });
            });
        }

        if ($request->tanggal) {
            $query->whereDate('tanggal_pengembalian', $request->tanggal);
        }

        $data = $query->latest()->paginate(10)->withQueryString();

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    /* ================= CREATE (Data untuk Form) ================= */
    public function create($hashid)
    {
        $id = $this->resolveHashid($hashid);
        $pengambilan = PengambilanAlat::with(['alat', 'pengembalians'])
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $pengambilan
        ]);
    }

    /* ================= STORE ================= */
    public function store(Request $request, $hashid)
    {
        try {
            Log::info('=== STORE PENGEMBALIAN START ===', [
                'hashid' => $hashid,
                'request' => $request->all(),
            ]);

            $id = $this->resolveHashid($hashid);
            $pengambilan = PengambilanAlat::with('pengembalians')
                ->findOrFail($id);

            // Hitung sisa pinjaman
            $totalSudahKembali = $pengambilan->pengembalians->sum('jumlah');
            $sisaPinjaman = $pengambilan->jumlah - $totalSudahKembali;

            Log::info('Sisa pinjaman calculated', [
                'total_pinjam' => $pengambilan->jumlah,
                'total_sudah_kembali' => $totalSudahKembali,
                'sisa' => $sisaPinjaman,
            ]);

            // Cek apakah terlambat
            $isTerlambat = $pengambilan->tanggal_jatuh_tempo
                && Carbon::parse($pengambilan->tanggal_jatuh_tempo)->isPast();

            // Validasi dinamis
            $rules = [
                'tanggal_pengembalian' => 'required|date',
            ];

            if ($isTerlambat) {
                $rules['keterangan'] = 'required|string|min:10';
                $rules['foto'] = 'required|image|mimes:jpeg,png,jpg,webp|max:2048';
            } else {
                $rules['keterangan'] = 'nullable|string';
                $rules['foto'] = 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048';
            }

            $validator = Validator::make($request->all(), $rules, [
                'keterangan.required' => 'Keterangan wajib diisi karena pengembalian melewati tenggat waktu.',
                'keterangan.min' => 'Keterangan minimal 10 karakter.',
                'foto.required' => 'Foto bukti wajib diupload karena pengembalian melewati tenggat waktu.',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 422);
            }

            $validated = $validator->validated();
            Log::info('Validation passed', ['validated' => $validated]);

            // Upload foto jika ada
            $fotoPath = null;
            if ($request->hasFile('foto')) {
                $fotoPath = $this->uploadFotoTransaksi($request->file('foto'), 'pengembalian');
                Log::info('Foto uploaded', ['path' => $fotoPath]);
            }

            $dataToInsert = [
                'pengambilan_alat_id' => $pengambilan->id,
                'user_id'             => Auth::id(),
                'jumlah'              => $sisaPinjaman,
                'tanggal_pengembalian' => $validated['tanggal_pengembalian'],
                'keterangan'          => $validated['keterangan'] ?? null,
                'foto'                => $fotoPath,
            ];

            Log::info('Data to insert', $dataToInsert);

            $pengembalian = null;
            DB::transaction(function () use ($pengambilan, $dataToInsert, $sisaPinjaman, &$pengembalian) {
                $pengembalian = PengembalianAlat::create($dataToInsert);

                Log::info('Pengembalian created', [
                    'id' => $pengembalian->id,
                    'data' => $pengembalian->toArray(),
                ]);

                // Kembalikan stok alat
                $alat = Alat::findOrFail($pengambilan->alat_id);
                $alat->increment('jumlah', $sisaPinjaman);

                Log::info('Stok alat incremented', [
                    'alat_id' => $alat->id,
                    'increment' => $sisaPinjaman,
                    'new_stok' => $alat->jumlah,
                ]);

                // Update status pengambilan
                $totalBaru = $pengambilan->pengembalians()->sum('jumlah') + $sisaPinjaman;
                if ($totalBaru >= $pengambilan->jumlah) {
                    $pengambilan->update(['status' => 'kembali']);
                    Log::info('Status pengambilan updated to: kembali');
                }
            });

            Log::info('=== STORE PENGEMBALIAN SUCCESS ===');

            return response()->json([
                'success' => true,
                'message' => 'Pengembalian berhasil disimpan.',
                'data' => $pengembalian->load('pengambilan.alat')
            ], 201);

        } catch (\Exception $e) {
            Log::error('=== STORE PENGEMBALIAN ERROR ===', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan pengembalian: ' . $e->getMessage()
            ], 500);
        }
    }

    /* ================= SHOW ================= */
    public function show($hashid)
    {
        $id = $this->resolveHashid($hashid);
        $data = PengembalianAlat::with(['pengambilan.alat', 'user'])
            ->findOrFail($id);

        if (Auth::user()->role !== 'admin' && Auth::id() !== $data->user_id) {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak.'
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    /* ================= EDIT (Data untuk Form Update) ================= */
    public function edit($hashid)
    {
        $id = $this->resolveHashid($hashid);
        $data = PengembalianAlat::with(['pengambilan.alat', 'pengambilan.pengembalians'])
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    /* ================= UPDATE ================= */
    public function update(Request $request, $hashid)
    {
        try {
            $id = $this->resolveHashid($hashid);
            $data = PengembalianAlat::with('pengambilan.pengembalians')->findOrFail($id);
            $pengambilan = $data->pengambilan;

            $totalSudahKembali = $pengambilan->pengembalians->sum('jumlah') - $data->jumlah;
            $sisaPinjaman = $pengambilan->jumlah - $totalSudahKembali;

            $isTerlambat = $pengambilan->tanggal_jatuh_tempo
                && Carbon::parse($pengambilan->tanggal_jatuh_tempo)->isPast();

            $rules = [
                'tanggal_pengembalian' => 'required|date',
            ];

            if ($isTerlambat) {
                $rules['keterangan'] = 'required|string|min:10';
                if (!$data->foto) {
                    $rules['foto'] = 'required|image|mimes:jpeg,png,jpg,webp|max:2048';
                } else {
                    $rules['foto'] = 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048';
                }
            } else {
                $rules['keterangan'] = 'nullable|string';
                $rules['foto'] = 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048';
            }

            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 422);
            }
            $validated = $validator->validated();

            DB::transaction(function () use ($data, $pengambilan, $request, $validated, $sisaPinjaman) {
                $alat = Alat::findOrFail($pengambilan->alat_id);

                $alat->decrement('jumlah', $data->jumlah);
                $alat->increment('jumlah', $sisaPinjaman);

                $fotoPath = $data->foto;
                if ($request->hasFile('foto')) {
                    if ($data->foto) {
                        $this->deleteFotoTransaksi($data->foto, 'pengembalian');
                    }
                    $fotoPath = $this->uploadFotoTransaksi($request->file('foto'), 'pengembalian');
                }

                $data->update([
                    'jumlah'              => $sisaPinjaman,
                    'tanggal_pengembalian'=> $validated['tanggal_pengembalian'],
                    'keterangan'          => $validated['keterangan'] ?? $data->keterangan,
                    'foto'                => $fotoPath,
                ]);

                $totalBaru = $pengambilan->pengembalians()->sum('jumlah');
                if ($totalBaru >= $pengambilan->jumlah) {
                    $pengambilan->update(['status' => 'kembali']);
                } else {
                    $pengambilan->update(['status' => 'dipinjam']);
                }
            });

            return response()->json([
                'success' => true,
                'message' => 'Data pengembalian berhasil diperbarui.',
                'data' => $data->fresh()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui data: ' . $e->getMessage()
            ], 500);
        }
    }

    /* ================= DESTROY ================= */
    public function destroy($hashid)
    {
        try {
            $id = $this->resolveHashid($hashid);
            $data = PengembalianAlat::with('pengambilan')->findOrFail($id);

            DB::transaction(function () use ($data) {
                $pengambilan = $data->pengambilan;
                $alat = Alat::findOrFail($pengambilan->alat_id);

                $alat->decrement('jumlah', $data->jumlah);

                if ($data->foto) {
                    $this->deleteFotoTransaksi($data->foto, 'pengembalian');
                }

                $data->delete();

                $total = $pengambilan->pengembalians()->sum('jumlah');
                if ($total < $pengambilan->jumlah) {
                    $pengambilan->update(['status' => 'dipinjam']);
                }
            });

            return response()->json([
                'success' => true,
                'message' => 'Data pengembalian dihapus.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus data: ' . $e->getMessage()
            ], 500);
        }
    }

    /* ================= EXPORT PDF ================= */
    public function exportPdf($hashid = null)
    {
        if ($hashid) {
            $id = $this->resolveHashid($hashid);
            $data = PengembalianAlat::with(['user', 'pengambilan.alat'])
                ->findOrFail($id);
            $list = collect([$data]);
        } else {
            $list = PengembalianAlat::with(['user', 'pengambilan.alat'])->get();
        }

        $pdf = Pdf::loadView('pengembalian_alat.export-pdf', compact('list'));

        // OPSI 1: Langsung download (Mobile app harus handle stream response)
        // return $pdf->download('pengembalian_alat.pdf');

        // OPSI 2 (DISARANKAN UNTUK MOBILE): Simpan ke storage dan kembalikan URL-nya
        $filename = 'pengembalian_' . time() . '_' . Str::random(5) . '.pdf';
        Storage::disk('public')->put($filename, $pdf->output());

        return response()->json([
            'success' => true,
            'message' => 'PDF berhasil dibuat.',
            'url' => Storage::disk('public')->url($filename)
        ]);
    }

        /* ================= FOTO HELPER (SIMPLE - NO THUMB) ================= */
        /* ================= FOTO HELPER (SIMPLE - NO THUMB) ================= */
    protected function uploadFotoTransaksi($file, string $folder): string
    {
        // 1. Generate nama file unik agar tidak bentrok
        $filename = date('YmdHis') . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();

        // 2. Simpan file ke disk 'public' di folder yang ditentukan
        // Path fisik akan menjadi: D:\laragon\www\BuhinCore\public\storage\$folder\$filename
        $path = $file->storeAs($folder, $filename, 'public');

        Log::info('📷 [uploadFotoTransaksi] File uploaded to: ' . $folder . '/' . $filename);

        return $filename;
    }

    protected function deleteFotoTransaksi($filename, string $folder): void
    {
        if ($filename) {
            // Hapus file dari disk 'public'
            Storage::disk('public')->delete($folder . '/' . $filename);
            Log::info('🗑️ [deleteFotoTransaksi] File deleted: ' . $folder . '/' . $filename);
        }
    }
    public function getByAlat($hashid, Request $request)
{
    try {
        $alatId = $this->resolveHashid($hashid);

        $query = PengembalianAlat::with(['pengambilan.alat', 'user'])
            ->whereHas('pengambilan', function ($q) use ($alatId) {
                $q->where('alat_id', $alatId);
            });

        $data = $query->latest()->paginate($request->get('per_page', 50));

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    } catch (ModelNotFoundException $e) {
        return response()->json([
            'success' => false,
            'message' => 'Alat tidak ditemukan.'
        ], 404);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Gagal mengambil data: ' . $e->getMessage()
        ], 500);
    }
}
}
