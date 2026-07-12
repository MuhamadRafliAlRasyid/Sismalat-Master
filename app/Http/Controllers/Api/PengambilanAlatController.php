<?php

namespace App\Http\Controllers\Api;

use App\Models\Alat;
use App\Models\Bagian;
use App\Models\PengambilanAlat;
use App\Models\User;
use App\Notifications\PeminjamanWarningNotification;
use App\Services\HashIdService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;
use Intervention\Image\ImageManager;

class PengambilanAlatController extends Controller
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

    // ✨ NORMALISASI NAMA - Lebih robust
    protected function normalizeNamaAlat(string $nama): string
    {
        $normalized = trim(preg_replace('/\s+/', ' ', $nama));
        $normalized = preg_replace('/\d+$/', '', $normalized);
        return trim($normalized);
    }

    // ✨ SANITASI STRING - Hapus karakter yang bermasalah
    protected function sanitizeString($value): string
    {
        if ($value === null || $value === '') return '-';
        $value = trim((string)$value);
        $value = preg_replace('/[\x00-\x1F\x7F]/', '', $value);
        $value = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
        return $value ?: '-';
    }

    // ✨ GROUPING ALAT - Dengan sanitasi ketat
    protected function groupAlatsForForm($alats): array
    {
        try {
            $grouped = $alats->groupBy(function ($alat) {
                return $this->normalizeNamaAlat($alat->nama_alat ?? 'Unknown');
            });

            return $grouped->map(function ($items, $namaGroup) {
                $displayNama = $items->pluck('nama_alat')
                    ->filter()
                    ->countBy()
                    ->sortDesc()
                    ->keys()
                    ->first() ?? $namaGroup;

                return [
                    'nama'         => $this->sanitizeString($namaGroup),
                    'display_nama' => $this->sanitizeString($displayNama),
                    'count'        => $items->count(),
                    'items'        => $items->map(function ($alat) {
                        return [
                            'hashid'    => $alat->hashid ?? '',
                            'id'        => $alat->id ?? 0,
                            'nama_alat' => $this->sanitizeString($alat->nama_alat),
                            'merk'      => $this->sanitizeString($alat->merk),
                            'tipe'      => $this->sanitizeString($alat->tipe),
                            'no_seri'   => $this->sanitizeString($alat->no_seri),
                            'kapasitas' => $this->sanitizeString($alat->kapasitas),
                            'kelas'     => $this->sanitizeString($alat->kelas),
                            'jumlah'    => (int)($alat->jumlah ?? 0),
                        ];
                    })->values()->all(),
                ];
            })->sortBy('display_nama')->values()->all();
        } catch (\Throwable $e) {
            Log::error('Error grouping alats: ' . $e->getMessage());
            return [];
        }
    }

    /* ================= INDEX ================= */
    public function index(Request $request)
    {
        $query = PengambilanAlat::with(['user', 'bagian', 'alat']);

        if (Auth::check() && Auth::user()->role !== 'admin') {
            $query->where('user_id', Auth::id());
        }

        if ($request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('waktu_pengambilan', 'like', "%{$search}%")
                    ->orWhereHas('user', fn($q) => $q->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('bagian', fn($q) => $q->where('nama', 'like', "%{$search}%"))
                    ->orWhereHas('alat', fn($q) => $q->where('nama_alat', 'like', "%{$search}%"));
            });
        }

        // ✨ CATATAN: Untuk API, disarankan memindahkan pengecekan warning ini ke
        // Laravel Scheduler (cronjob) agar tidak membebani setiap request index.
        // $this->checkPeminjamanWarning();

        $data = $query->latest()->paginate(10)->withQueryString();

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    /* ================= CREATE (Data untuk Form) ================= */
    public function create(Request $request)
    {
        $users = User::all();
        $bagians = Bagian::all();
        $alatsGrouped = $this->groupAlatsForForm(Alat::whereNull('deleted_at')->get());

        $selectedHashid = null;

        if ($request->has('alat_hashid')) {
            $id = app(HashIdService::class)->decode($request->alat_hashid);
            if ($id) {
                $selectedHashid = $request->alat_hashid;
            }
        }

        if (Auth::user()->role !== 'admin') {
            $users = User::where('id', Auth::id())->get();
            $bagians = Auth::user()->bagian
                ? Bagian::where('id', Auth::user()->bagian->id)->get()
                : collect();
        }

        return response()->json([
            'success' => true,
            'data' => [
                'users' => $users,
                'bagians' => $bagians,
                'alatsGrouped' => $alatsGrouped,
                'selectedHashid' => $selectedHashid
            ]
        ]);
    }

    /* ================= STORE ================= */
    public function store(Request $request)
    {
        Log::info('START STORE PENGAMBILAN ALAT', ['request' => $request->all()]);

        try {
            if (Auth::user()->role !== 'admin') {
                $request->merge([
                    'user_id'   => Auth::id(),
                    'bagian_id' => Auth::user()->bagian_id ?? null,
                ]);
            }

            $validator = Validator::make($request->all(), [
                'user_id'            => 'required|exists:users,id',
                'bagian_id'          => 'required|exists:bagian,id',
                'alat_id'            => 'required|string',
                'nama_peminjam'      => 'nullable|string|max:255',
                'jumlah'             => 'required|integer|min:1',
                'satuan'             => 'required|string|max:20',
                'lama_pinjam'        => 'required|integer|min:1',
                'keperluan'          => 'required|string|max:255',
                'waktu_pengambilan'  => 'required|date',
                'foto'               => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 422);
            }

            $validated = $validator->validated();

            if ($request->hasFile('foto')) {
                $validated['foto'] = $this->uploadFotoTransaksi($request->file('foto'), 'pengambilan');
            }

            $pengambilan = null;
            DB::transaction(function () use ($validated, &$pengambilan) {
                $alatId = $this->resolveHashid($validated['alat_id']);
                $alat = Alat::findOrFail($alatId);

                if ($alat->jumlah < $validated['jumlah']) {
                    throw new \Exception('Stok alat tidak mencukupi');
                }

                $alat->decrement('jumlah', $validated['jumlah']);

                $validated['alat_id'] = $alatId;
                $validated['status']  = 'dipinjam';

                $pengambilan = PengambilanAlat::create($validated);
            });

            return response()->json([
                'success' => true,
                'message' => 'Pengambilan alat berhasil ditambahkan.',
                'data' => $pengambilan->load(['user', 'bagian', 'alat'])
            ], 201);
        } catch (\Throwable $e) {
            Log::error('ERROR STORE PENGAMBILAN ALAT', [
                'message' => $e->getMessage(),
                'line'    => $e->getLine(),
                'file'    => $e->getFile()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Terjadi error: ' . $e->getMessage()
            ], 500);
        }
    }

    /* ================= SHOW ================= */
    public function show($hashid)
    {
        $data = PengambilanAlat::with(['user', 'bagian', 'alat'])
            ->findOrFail($this->resolveHashid($hashid));

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

    /* ================= EXPORT PDF ================= */
    public function exportPdf($hashid = null)
    {
        if ($hashid) {
            $data = PengambilanAlat::with(['user', 'alat'])
                ->findOrFail($this->resolveHashid($hashid));
            $list = collect([$data]);
        } else {
            $list = PengambilanAlat::with(['user', 'alat'])->get();
        }

        $pdf = Pdf::loadView('pengambilan_alat.export-pdf', compact('list'));

        // Simpan ke storage dan kembalikan URL-nya untuk mobile
        $filename = 'pengambilan_' . time() . '_' . Str::random(5) . '.pdf';
        Storage::disk('public')->put($filename, $pdf->output());

        return response()->json([
            'success' => true,
            'message' => 'PDF berhasil dibuat.',
            'url' => Storage::disk('public')->url($filename)
        ]);
    }

    /* ================= EDIT (Data untuk Form Update) ================= */
    public function edit($hashid)
    {
        $data = PengambilanAlat::findOrFail($this->resolveHashid($hashid));

        if (Auth::user()->role !== 'admin' && Auth::id() !== $data->user_id) {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak.'
            ], 403);
        }

        $users   = User::all();
        $bagians = Bagian::all();
        $alatsGrouped = $this->groupAlatsForForm(Alat::whereNull('deleted_at')->get());
        $selectedHashid = $data->alat ? $data->alat->hashid : '';

        return response()->json([
            'success' => true,
            'data' => [
                'pengambilan' => $data,
                'users' => $users,
                'bagians' => $bagians,
                'alatsGrouped' => $alatsGrouped,
                'selectedHashid' => $selectedHashid
            ]
        ]);
    }

    /* ================= UPDATE ================= */
    public function update(Request $request, $hashid)
    {
        try {
            $data = PengambilanAlat::findOrFail($this->resolveHashid($hashid));

            if (Auth::user()->role !== 'admin' && Auth::id() !== $data->user_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Akses ditolak.'
                ], 403);
            }

            $validator = Validator::make($request->all(), [
                'bagian_id'          => 'required|exists:bagian,id',
                'alat_id'            => 'required|string',
                'nama_peminjam'      => 'nullable|string|max:255',
                'jumlah'             => 'required|integer|min:1',
                'satuan'             => 'required|string|max:20',
                'lama_pinjam'        => 'required|integer|min:1',
                'keperluan'          => 'required|string|max:255',
                'waktu_pengambilan'  => 'required|date',
                'foto'               => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 422);
            }

            $validated = $validator->validated();

            if ($request->hasFile('foto')) {
                if ($data->foto) {
                    $this->deleteFotoTransaksi($data->foto, 'pengambilan');
                }
                $validated['foto'] = $this->uploadFotoTransaksi($request->file('foto'), 'pengambilan');
            }

            $validated['user_id'] = $data->user_id;

            DB::transaction(function () use ($data, $validated) {
                $oldAlat   = Alat::findOrFail($data->alat_id);
                $newAlatId = $this->resolveHashid($validated['alat_id']);
                $newAlat   = Alat::findOrFail($newAlatId);

                $oldAlat->increment('jumlah', $data->jumlah);

                if ($newAlat->jumlah < $validated['jumlah']) {
                    throw new \Exception('Stok alat tidak mencukupi');
                }

                $newAlat->decrement('jumlah', $validated['jumlah']);

                $validated['alat_id'] = $newAlatId;
                $data->update($validated);
            });

            return response()->json([
                'success' => true,
                'message' => 'Data berhasil diperbarui',
                'data' => $data->fresh()->load(['user', 'bagian', 'alat'])
            ]);
        } catch (\Throwable $e) {
            Log::error('ERROR UPDATE PENGAMBILAN ALAT', [
                'message' => $e->getMessage(),
                'line'    => $e->getLine(),
                'file'    => $e->getFile()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Terjadi error: ' . $e->getMessage()
            ], 500);
        }
    }

    /* ================= DESTROY ================= */
    public function destroy($hashid)
    {
        try {
            $data = PengambilanAlat::findOrFail($this->resolveHashid($hashid));

            if (Auth::user()->role !== 'admin' && Auth::id() !== $data->user_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Akses ditolak.'
                ], 403);
            }

            DB::transaction(function () use ($data) {
                $alat = Alat::find($data->alat_id);
                if ($alat) {
                    $alat->increment('jumlah', $data->jumlah);
                }
                if ($data->foto) {
                    $this->deleteFotoTransaksi($data->foto, 'pengambilan');
                }
                $data->delete();
            });

            return response()->json([
                'success' => true,
                'message' => 'Data berhasil dihapus'
            ]);
        } catch (\Throwable $e) {
            Log::error('ERROR DESTROY PENGAMBILAN ALAT', [
                'message' => $e->getMessage(),
                'line'    => $e->getLine(),
                'file'    => $e->getFile()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Terjadi error: ' . $e->getMessage()
            ], 500);
        }
    }

    /* ================= CEK PEMINJAMAN WARNING ================= */
    public function checkPeminjamanWarning(): void
    {
        try {
            Log::info('checkPeminjamanWarning dijalankan pada: ' . now());

            $pengambilans = PengambilanAlat::with(['user', 'alat'])
                ->where('status', 'dipinjam')
                ->whereNotNull('lama_pinjam')
                ->where('lama_pinjam', '>', 0)
                ->get();

            $notified15Percent = 0;
            $notified1Day = 0;
            $skipped = 0;
            $errors = 0;

            foreach ($pengambilans as $pengambilan) {
                try {
                    if (!$pengambilan->user) {
                        $skipped++;
                        continue;
                    }

                    $shouldSendNotification = false;
                    $warningType = null;

                    if ($pengambilan->should_warn_1day) {
                        $shouldSendNotification = true;
                        $warningType = '1_day';
                    } elseif ($pengambilan->should_warn_15) {
                        $shouldSendNotification = true;
                        $warningType = '15_percent';
                    }

                    if (!$shouldSendNotification) {
                        $skipped++;
                        continue;
                    }

                    $pengambilan->user->notify(new PeminjamanWarningNotification($pengambilan, $warningType));

                    if ($warningType === '1_day') {
                        $pengambilan->markWarning1DaySent();
                        $notified1Day++;
                    } else {
                        $pengambilan->markWarning15Sent();
                        $notified15Percent++;
                    }

                } catch (\Exception $e) {
                    $errors++;
                    Log::error("Gagal kirim notifikasi untuk pengambilan {$pengambilan->id}: " . $e->getMessage());
                }
            }

            $totalNotified = $notified1Day + $notified15Percent;
            Log::info("CheckPeminjamanWarning selesai. Total: {$totalNotified} terkirim.");

        } catch (\Exception $e) {
            Log::error('Error di checkPeminjamanWarning: ' . $e->getMessage());
        }
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
