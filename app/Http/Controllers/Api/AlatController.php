<?php

namespace App\Http\Controllers\Api;

use App\Exports\AlatExport;
use App\Models\Alat;
use App\Models\KalibrasiAlat;
use App\Models\PengambilanAlat;
use App\Models\PengembalianAlat;
use App\Models\User;
use App\Services\HashIdService;
use Carbon\Carbon;
use Endroid\QrCode\Color\Color;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;
use Maatwebsite\Excel\Facades\Excel;

class AlatController extends Controller
{
    /**
     * ✅ Helper: Generate URL untuk Mobile
     * Convert URL storage ke IP LAN agar bisa diakses dari HP
     */
    protected function _getMobileUrl($path)
    {
        if (!$path) return null;

        // ✅ Gunakan IP LAN server (ganti dengan IP server Anda)
        $baseUrl = 'http://192.168.1.10:8000';

        return $baseUrl . '/storage/' . $path;
    }

    protected function resolveHashid(string $hashid): Alat
    {
        $id = app(HashIdService::class)->decode($hashid);
        if (!$id) {
            throw new ModelNotFoundException('Alat tidak ditemukan.');
        }
        return Alat::findOrFail($id);
    }

    protected function resolveHashidWithTrashed(string $hashid): Alat
    {
        $id = app(HashIdService::class)->decode($hashid);
        if (!$id) {
            throw new ModelNotFoundException('Alat tidak ditemukan.');
        }
        return Alat::withTrashed()->findOrFail($id);
    }

    protected function normalizeNamaAlat(string $nama): string
    {
        $normalized = trim(preg_replace('/\s+/', ' ', $nama));
        $normalized = preg_replace('/\d+$/', '', $normalized);
        return trim($normalized);
    }

    /* ================= INDEX ================= */
    public function index(Request $request)
    {
        Log::info('🔍 [AlatController@index] Request received', [
            'params' => $request->all(),
            'user_id' => auth()->id(),
        ]);

        try {
            $query = Alat::with('kategori');

            if ($request->search) {
                Log::info('🔍 [AlatController@index] Searching for: ' . $request->search);
                $query->where(function ($q) use ($request) {
                    $q->where('nama_alat', 'like', '%' . $request->search . '%')
                      ->orWhere('merk', 'like', '%' . $request->search . '%')
                      ->orWhere('tipe', 'like', '%' . $request->search . '%');
                });
            }

            if ($request->kategori_id) {
                Log::info('🔍 [AlatController@index] Filtering by kategori_id: ' . $request->kategori_id);
                $query->where('kategori_id', $request->kategori_id);
            }

            // ✅ Gunakan pagination biasa untuk API
            $data = $query->orderBy('nama_alat')->paginate(12);

            Log::info('🔍 [AlatController@index] Total alat found: ' . $data->total());

            // ✅ Transform data untuk menambahkan URL foto dan QR code (Mobile Compatible)
            $data->getCollection()->transform(function ($alat) {
                return [
                    'id' => $alat->id,
                    'hashid' => $alat->hashid,
                    'nama_alat' => $alat->nama_alat,
                    'merk' => $alat->merk,
                    'tipe' => $alat->tipe,
                    'no_seri' => $alat->no_seri,
                    'jumlah' => $alat->jumlah,
                    'kategori' => $alat->kategori,

                    // ✅ PERBAIKAN: Return URL dengan IP LAN (Mobile Compatible)
                    'foto_url' => $alat->foto ? $this->_getMobileUrl('alat/' . $alat->foto) : null,
                    'foto_thumb_url' => $alat->foto ? $this->_getMobileUrl('alat/thumb_' . $alat->foto) : null,
                    'qr_code_url' => $alat->qr_code ? $this->_getMobileUrl($alat->qr_code) : null,

                    'masa_berlaku' => $alat->masa_berlaku,
                    'created_at' => $alat->created_at,
                    'updated_at' => $alat->updated_at,
                ];
            });

            $kategoris = \App\Models\Kategori::all();

            Log::info('✅ [AlatController@index] Success - returning data', [
                'total_alat' => $data->total(),
                'total_kategoris' => $kategoris->count(),
            ]);

            return response()->json([
                'success' => true,
                'data' => $data,
                'kategoris' => $kategoris
            ]);
        } catch (\Exception $e) {
            Log::error('❌ [AlatController@index] Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data alat: ' . $e->getMessage(),
                'data' => []
            ], 500);
        }
    }

    /* ================= DAFTAR RIWAYAT ================= */
    public function daftarRiwayat(Request $request)
    {
        Log::info('🔍 [AlatController@daftarRiwayat] Request received', [
            'params' => $request->all(),
        ]);

        try {
            $pengambilanIds = PengambilanAlat::select('alat_id')->distinct()->pluck('alat_id');
            $pengembalianIds = PengembalianAlat::with('pengambilan')
                                ->get()
                                ->pluck('pengambilan.alat_id')
                                ->filter()
                                ->unique();
            $kalibrasiIds = KalibrasiAlat::select('alat_id')->distinct()->pluck('alat_id');

            $alatIds = $pengambilanIds
                        ->merge($pengembalianIds)
                        ->merge($kalibrasiIds)
                        ->unique();

            $query = Alat::with(['kategori', 'pengambilan', 'kalibrasis', 'pengembalian'])
                        ->whereIn('id', $alatIds);

            if ($request->filled('search')) {
                $query->where(function ($q) use ($request) {
                    $q->where('nama_alat', 'like', '%' . $request->search . '%')
                      ->orWhere('merk', 'like', '%' . $request->search . '%');
                });
            }

            if ($request->filled('status')) {
                if ($request->status === 'dipinjam') {
                    $query->whereHas('pengambilan', function ($q) {
                        $q->where('status', 'dipinjam');
                    });
                } elseif ($request->status === 'dikembalikan') {
                    $query->whereHas('pengembalian');
                } elseif ($request->status === 'dikalibrasi') {
                    $query->whereHas('kalibrasis');
                }
            }

            $alats = $query->orderBy('nama_alat')->paginate(12);

            // ✅ Transform dengan Mobile Compatible URL
            $alats->getCollection()->transform(function ($alat) {
                $alat->foto_url = $alat->foto ? $this->_getMobileUrl('alat/' . $alat->foto) : null;
                $alat->foto_thumb_url = $alat->foto ? $this->_getMobileUrl('alat/thumb_' . $alat->foto) : null;
                $alat->qr_code_url = $alat->qr_code ? $this->_getMobileUrl($alat->qr_code) : null;
                return $alat;
            });

            Log::info('✅ [AlatController@daftarRiwayat] Success', [
                'total' => $alats->total(),
            ]);

            return response()->json([
                'success' => true,
                'data' => $alats
            ]);
        } catch (\Exception $e) {
            Log::error('❌ [AlatController@daftarRiwayat] Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data riwayat: ' . $e->getMessage(),
                'data' => []
            ], 500);
        }
    }

    /* ================= RIWAYAT DETAIL ================= */
    public function riwayat($hashid)
    {
        Log::info('🔍 [AlatController@riwayat] Request for hashid: ' . $hashid);

        try {
            $alat = $this->resolveHashid($hashid);
            $alat->load('kategori');

            $pengambilan = PengambilanAlat::with(['user', 'bagian'])
                            ->where('alat_id', $alat->id)
                            ->latest()
                            ->get();

            $pengembalian = PengembalianAlat::with(['pengambilan', 'user'])
                            ->whereHas('pengambilan', function($q) use ($alat) {
                                $q->where('alat_id', $alat->id);
                            })
                            ->latest()
                            ->get();

            $kalibrasis = KalibrasiAlat::where('alat_id', $alat->id)
                            ->latest()
                            ->get();

            // ✅ Transform dengan Mobile Compatible URL
            $alat->foto_url = $alat->foto ? $this->_getMobileUrl('alat/' . $alat->foto) : null;
            $alat->foto_thumb_url = $alat->foto ? $this->_getMobileUrl('alat/thumb_' . $alat->foto) : null;
            $alat->qr_code_url = $alat->qr_code ? $this->_getMobileUrl($alat->qr_code) : null;

            Log::info('✅ [AlatController@riwayat] Success', [
                'alat_id' => $alat->id,
                'pengambilan_count' => $pengambilan->count(),
                'pengembalian_count' => $pengembalian->count(),
                'kalibrasi_count' => $kalibrasis->count(),
            ]);

            return response()->json([
                'success' => true,
                'data' => [
                    'alat' => $alat,
                    'pengambilan' => $pengambilan,
                    'pengembalian' => $pengembalian,
                    'kalibrasis' => $kalibrasis
                ]
            ]);
        } catch (ModelNotFoundException $e) {
            Log::error('❌ [AlatController@riwayat] Not found: ' . $hashid);
            return response()->json([
                'success' => false,
                'message' => 'Alat tidak ditemukan.'
            ], 404);
        } catch (\Exception $e) {
            Log::error('❌ [AlatController@riwayat] Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data riwayat: ' . $e->getMessage()
            ], 500);
        }
    }

    /* ================= CREATE (Data untuk Form) ================= */
    public function create()
    {
        Log::info('🔍 [AlatController@create] Request received');

        try {
            $kategoris = \App\Models\Kategori::all();

            return response()->json([
                'success' => true,
                'data' => [
                    'kategoris' => $kategoris,
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('❌ [AlatController@create] Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data form: ' . $e->getMessage()
            ], 500);
        }
    }

    /* ================= STORE ================= */
    public function store(Request $request)
    {
        Log::info('🔍 [AlatController@store] Request received', [
            'data' => $request->except(['foto', 'password']),
            'has_file' => $request->hasFile('foto'),
        ]);

        try {
            $validator = Validator::make($request->all(), [
                'nama_alat'     => 'required|string|max:255',
                'kelas'         => 'nullable|string|max:100',
                'merk'          => 'required|string|max:255',
                'tipe'          => 'nullable|string|max:255',
                'no_seri'       => 'nullable|string|max:255',
                'no_identitas'  => 'nullable|string|max:255',
                'kapasitas'     => 'nullable|string|max:255',
                'daya_baca'     => 'nullable|string|max:255',
                'jumlah'        => 'required|integer|min:1',
                'no_sertifikat' => 'nullable|string|max:255',
                'kategori_id'   => 'nullable|exists:kategoris,id',
                'masa_berlaku'  => 'nullable|date',
                'foto'          => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            ]);

            if ($validator->fails()) {
                Log::warning('⚠️ [AlatController@store] Validation failed', [
                    'errors' => $validator->errors(),
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 422);
            }

            $data = $validator->validated();

            if ($request->hasFile('foto')) {
                Log::info('📷 [AlatController@store] Uploading foto');
                $data['foto'] = $this->uploadAndResizeFoto($request->file('foto'));
            }

            $alat = Alat::create($data);
            $this->generateQrCode($alat);

            $alat->load('kategori');

            // ✅ Transform dengan Mobile Compatible URL
            $alat->foto_url = $alat->foto ? $this->_getMobileUrl('alat/' . $alat->foto) : null;
            $alat->foto_thumb_url = $alat->foto ? $this->_getMobileUrl('alat/thumb_' . $alat->foto) : null;
            $alat->qr_code_url = $alat->qr_code ? $this->_getMobileUrl($alat->qr_code) : null;

            Log::info('✅ [AlatController@store] Success - Alat created', [
                'alat_id' => $alat->id,
                'hashid' => $alat->hashid,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Alat berhasil ditambahkan.',
                'data' => $alat
            ], 201);
        } catch (\Exception $e) {
            Log::error('❌ [AlatController@store] Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Gagal menambahkan alat: ' . $e->getMessage()
            ], 500);
        }
    }

    /* ================= SHOW ================= */
    public function show(string $hashid)
    {
        Log::info('🔍 [AlatController@show] Request for hashid: ' . $hashid);

        try {
            $alat = $this->resolveHashid($hashid);
            $alat->load('kategori', 'kalibrasis');

            // ✅ Transform dengan Mobile Compatible URL
            $alat->foto_url = $alat->foto ? $this->_getMobileUrl('alat/' . $alat->foto) : null;
            $alat->foto_thumb_url = $alat->foto ? $this->_getMobileUrl('alat/thumb_' . $alat->foto) : null;
            $alat->qr_code_url = $alat->qr_code ? $this->_getMobileUrl($alat->qr_code) : null;

            Log::info('✅ [AlatController@show] Success', [
                'alat_id' => $alat->id,
            ]);

            return response()->json([
                'success' => true,
                'data' => $alat
            ]);
        } catch (ModelNotFoundException $e) {
            Log::error('❌ [AlatController@show] Not found: ' . $hashid);
            return response()->json([
                'success' => false,
                'message' => 'Alat tidak ditemukan.'
            ], 404);
        } catch (\Exception $e) {
            Log::error('❌ [AlatController@show] Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data: ' . $e->getMessage()
            ], 500);
        }
    }

    /* ================= EDIT ================= */
    public function edit(string $hashid)
    {
        Log::info('🔍 [AlatController@edit] Request for hashid: ' . $hashid);

        try {
            $alat = $this->resolveHashid($hashid);
            $kategoris = \App\Models\Kategori::all();

            // ✅ Transform dengan Mobile Compatible URL
            $alat->foto_url = $alat->foto ? $this->_getMobileUrl('alat/' . $alat->foto) : null;
            $alat->foto_thumb_url = $alat->foto ? $this->_getMobileUrl('alat/thumb_' . $alat->foto) : null;
            $alat->qr_code_url = $alat->qr_code ? $this->_getMobileUrl($alat->qr_code) : null;

            return response()->json([
                'success' => true,
                'data' => [
                    'alat' => $alat,
                    'kategoris' => $kategoris
                ]
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Alat tidak ditemukan.'
            ], 404);
        }
    }

    /* ================= UPDATE ================= */
    public function update(Request $request, string $hashid)
    {
        Log::info('🔍 [AlatController@update] Request for hashid: ' . $hashid, [
            'data' => $request->except(['foto']),
        ]);

        try {
            $alat = $this->resolveHashid($hashid);

            $validator = Validator::make($request->all(), [
                'nama_alat'     => 'required|string|max:255',
                'kelas'         => 'nullable|string|max:100',
                'merk'          => 'required|string|max:255',
                'tipe'          => 'nullable|string|max:255',
                'no_seri'       => 'nullable|string|max:255',
                'no_identitas'  => 'nullable|string|max:255',
                'kapasitas'     => 'nullable|string|max:255',
                'daya_baca'     => 'nullable|string|max:255',
                'jumlah'        => 'required|integer|min:1',
                'no_sertifikat' => 'nullable|string|max:255',
                'kategori_id'   => 'nullable|exists:kategoris,id',
                'masa_berlaku'  => 'nullable|date',
                'foto'          => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 422);
            }

            $data = $validator->validated();

            if ($request->hasFile('foto')) {
                $this->deleteFoto($alat);
                $data['foto'] = $this->uploadAndResizeFoto($request->file('foto'));
            }

            $alat->update($data);

            if ($alat->wasChanged('nama_alat')) {
                $this->generateQrCode($alat);
            }

            $alat->load('kategori');

            // ✅ Transform dengan Mobile Compatible URL
            $alat->foto_url = $alat->foto ? $this->_getMobileUrl('alat/' . $alat->foto) : null;
            $alat->foto_thumb_url = $alat->foto ? $this->_getMobileUrl('alat/thumb_' . $alat->foto) : null;
            $alat->qr_code_url = $alat->qr_code ? $this->_getMobileUrl($alat->qr_code) : null;

            Log::info('✅ [AlatController@update] Success', [
                'alat_id' => $alat->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Alat berhasil diperbarui.',
                'data' => $alat
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Alat tidak ditemukan.'
            ], 404);
        } catch (\Exception $e) {
            Log::error('❌ [AlatController@update] Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui alat: ' . $e->getMessage()
            ], 500);
        }
    }

    /* ================= DESTROY ================= */
    public function destroy(string $hashid)
    {
        Log::info('🔍 [AlatController@destroy] Request for hashid: ' . $hashid);

        try {
            $alat = $this->resolveHashid($hashid);
            $alat->delete();

            Log::info('✅ [AlatController@destroy] Success - Alat soft deleted', [
                'alat_id' => $alat->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Alat dipindahkan ke tempat sampah.'
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Alat tidak ditemukan.'
            ], 404);
        } catch (\Exception $e) {
            Log::error('❌ [AlatController@destroy] Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus alat: ' . $e->getMessage()
            ], 500);
        }
    }

    /* ================= TRASHED ================= */
    public function trashed()
    {
        Log::info('🔍 [AlatController@trashed] Request received');

        try {
            $data = Alat::onlyTrashed()->paginate(10);

            // ✅ Transform dengan Mobile Compatible URL
            $data->getCollection()->transform(function ($alat) {
                $alat->foto_url = $alat->foto ? $this->_getMobileUrl('alat/' . $alat->foto) : null;
                $alat->foto_thumb_url = $alat->foto ? $this->_getMobileUrl('alat/thumb_' . $alat->foto) : null;
                $alat->qr_code_url = $alat->qr_code ? $this->_getMobileUrl($alat->qr_code) : null;
                return $alat;
            });

            return response()->json([
                'success' => true,
                'data' => $data
            ]);
        } catch (\Exception $e) {
            Log::error('❌ [AlatController@trashed] Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data trash: ' . $e->getMessage()
            ], 500);
        }
    }

    /* ================= RESTORE ================= */
    public function restore(string $hashid)
    {
        Log::info('🔍 [AlatController@restore] Request for hashid: ' . $hashid);

        try {
            $alat = $this->resolveHashidWithTrashed($hashid);
            $alat->restore();

            Log::info('✅ [AlatController@restore] Success', [
                'alat_id' => $alat->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Alat berhasil dipulihkan.'
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Alat tidak ditemukan.'
            ], 404);
        }
    }

    /* ================= FORCE DELETE ================= */
    public function forceDelete(string $hashid)
    {
        Log::info('🔍 [AlatController@forceDelete] Request for hashid: ' . $hashid);

        try {
            $alat = $this->resolveHashidWithTrashed($hashid);

            if ($alat->qr_code && Storage::disk('public')->exists($alat->qr_code)) {
                Storage::disk('public')->delete($alat->qr_code);
            }

            $this->deleteFoto($alat);
            $alat->forceDelete();

            Log::info('✅ [AlatController@forceDelete] Success - Permanent delete', [
                'alat_id' => $alat->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Alat dihapus secara permanen.'
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Alat tidak ditemukan.'
            ], 404);
        }
    }

    /* ================= GENERATE QR CODE ================= */
    public function generateQrCode(Alat $alat): void
    {
        Storage::makeDirectory('public/qrcodes', 0755, true);

        $qrCodePath = 'qrcodes/alat_' . $alat->hashid . '_' . Str::slug($alat->nama_alat) . '.png';
        $fullPath = storage_path('app/public/' . $qrCodePath);

        if ($alat->qr_code && Storage::disk('public')->exists($alat->qr_code)) {
            Storage::disk('public')->delete($alat->qr_code);
        }

        try {
            // ✅ PERBAIKAN: QR Code URL dengan IP LAN (Mobile Compatible)
            $qrData = 'http://192.168.1.10:8000/login?alat_id=' . $alat->hashid;

            $qrCode = new QrCode(
                data: $qrData,
                encoding: new Encoding('UTF-8'),
                size: 300,
                margin: 10,
                foregroundColor: new Color(0, 0, 0),
                backgroundColor: new Color(255, 255, 255)
            );

            $writer = new PngWriter();
            $result = $writer->write($qrCode);
            $result->saveToFile($fullPath);

            $alat->update(['qr_code' => $qrCodePath]);

            Log::info('✅ QR Code generated for alat', [
                'alat_id' => $alat->id,
                'path' => $qrCodePath,
            ]);
        } catch (\Exception $e) {
            Log::error('❌ QR Generation failed for alat ' . $alat->id . ': ' . $e->getMessage());
            throw $e;
        }
    }

    /* ================= UPLOAD FOTO ================= */
    protected function uploadAndResizeFoto($file): string
    {
        if (!class_exists(GdDriver::class)) {
            $filename = date('YmdHis') . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
            Storage::disk('public')->putFileAs('alat', $file, $filename);
            Storage::disk('public')->putFileAs('alat/thumb', $file, $filename);
            return $filename;
        }

        $manager = new ImageManager(new GdDriver());
        $image = $manager->read($file);

        $filename = date('YmdHis') . '_' . Str::random(10) . '.webp';

        Storage::disk('public')->put(
            'alat/' . $filename,
            $image->scaleDown(width: 1200)->toWebp(80)->toFilePointer()
        );

        Storage::disk('public')->put(
            'alat/thumb_' . $filename,
            $image->cover(200, 200)->toWebp(60)->toFilePointer()
        );

        return $filename;
    }

    /* ================= DELETE FOTO ================= */
    protected function deleteFoto(Alat $alat): void
    {
        if ($alat->foto) {
            Storage::disk('public')->delete('alat/' . $alat->foto);
            Storage::disk('public')->delete('alat/thumb_' . $alat->foto);
        }
    }

    /* ================= EXPORT EXCEL ================= */
    public function exportExcel()
    {
        Log::info('🔍 [AlatController@exportExcel] Request received');

        try {
            $filename = 'alat_export_' . time() . '.xlsx';
            $path = 'exports/' . $filename;

            Excel::store(new AlatExport, $path, 'public');

            // ✅ Return URL dengan IP LAN (Mobile Compatible)
            $url = $this->_getMobileUrl($path);

            Log::info('✅ [AlatController@exportExcel] Success', [
                'url' => $url,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'File Excel berhasil dibuat.',
                'url' => $url
            ]);
        } catch (\Exception $e) {
            Log::error('❌ [AlatController@exportExcel] Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal export Excel: ' . $e->getMessage()
            ], 500);
        }
    }
}
