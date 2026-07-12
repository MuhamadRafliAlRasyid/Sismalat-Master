<?php

namespace App\Http\Controllers;

use App\Exports\AlatExport;
use App\Models\Alat;
use App\Models\KalibrasiAlat;
use App\Models\PengambilanAlat;
use App\Models\PengembalianAlat;
use App\Models\User;
use App\Notifications\AlatExpiredNotification;
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
use Illuminate\Support\Str;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;
use Maatwebsite\Excel\Facades\Excel;

class AlatsController extends Controller
{
    protected function resolveHashid(string $hashid): Alat
    {
        $id = app(HashIdService::class)->decode($hashid);
        abort_if(!$id, 404);
        return Alat::findOrFail($id);
    }

    protected function resolveHashidWithTrashed(string $hashid): Alat
    {
        $id = app(HashIdService::class)->decode($hashid);
        abort_if(!$id, 404);
        return Alat::withTrashed()->findOrFail($id);
    }

    /**
     * Normalisasi nama alat untuk grouping
     * - Trim spasi di awal/akhir
     * - Replace multiple spaces dengan single space
     * - Remove trailing numbers (untuk kasus 'Anak Timbangan1')
     */
    protected function normalizeNamaAlat(string $nama): string
    {
        // Trim dan replace multiple spaces
        $normalized = trim(preg_replace('/\s+/', ' ', $nama));

        // Remove trailing numbers (angka di akhir nama)
        $normalized = preg_replace('/\d+$/', '', $normalized);

        // Trim lagi setelah remove numbers
        return trim($normalized);
    }

    public function index(Request $request)
    {
        $query = Alat::with('kategori');

        if ($request->search) {
            $query->search($request->search);
        }
        if ($request->kategori_id) {
            $query->where('kategori_id', $request->kategori_id);
        }

        // 1. Ambil semua data
        $allAlats = $query->orderBy('nama_alat')->get();

        // 2. Group by nama_alat yang sudah dinormalisasi
        $groupedAlats = $allAlats->groupBy(function ($alat) {
            return $this->normalizeNamaAlat($alat->nama_alat);
        });

        // 3. Setup paginasi manual
        $perPage = 12;
        $currentPage = Paginator::resolveCurrentPage('page');
        $currentItems = $groupedAlats->slice(($currentPage - 1) * $perPage, $perPage)->values();

        $alats = new LengthAwarePaginator(
            $currentItems,
            $groupedAlats->count(),
            $perPage,
            $currentPage,
            [
                'path'  => Paginator::resolveCurrentPath(),
                'query' => $request->query(),
            ]
        );

        // 4. Hitung nama alat pertama per halaman (untuk custom pagination)
        $pageNames = [];
        $allNames = $groupedAlats->keys()->values();
        for ($i = 0; $i < $allNames->count(); $i += $perPage) {
            $pageNum = ($i / $perPage) + 1;
            $pageNames[$pageNum] = $allNames[$i];
        }
        $alats->pageNames = $pageNames;

        $kategoris = \App\Models\Kategori::all();
        $this->checkExpired();

        return view('alats.index', compact('alats', 'kategoris'));
    }

    public function daftarRiwayat(Request $request)
    {
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
            $query->search($request->search);
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

        $alats = $query->orderBy('nama_alat')
                       ->paginate(12)
                       ->appends($request->only('search', 'status'));

        return view('alats.riwayat-index', compact('alats'));
    }

    public function riwayat($hashid)
    {
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

        return view('alats.riwayat', compact('alat', 'pengambilan', 'pengembalian', 'kalibrasis'));
    }

    public function create()
    {
        $kategoris = \App\Models\Kategori::all();
        return view('alats.create', compact('kategoris'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
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

        if ($request->hasFile('foto')) {
            $data['foto'] = $this->uploadAndResizeFoto($request->file('foto'));
        }

        $alat = Alat::create($data);
        $this->generateQrCode($alat);

        return redirect()->route('alats.index')
            ->with('success', 'Alat berhasil ditambahkan.');
    }

    public function show(string $hashid)
    {
        $alat = $this->resolveHashid($hashid);
        $alat->load('kategori', 'kalibrasis');
        return view('alats.show', compact('alat'));
    }

    public function edit(string $hashid)
    {
        $alat = $this->resolveHashid($hashid);
        $kategoris = \App\Models\Kategori::all();
        return view('alats.edit', compact('alat', 'kategoris'));
    }

    public function update(Request $request, string $hashid)
    {
        $alat = $this->resolveHashid($hashid);

        $data = $request->validate([
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

        if ($request->hasFile('foto')) {
            $this->deleteFoto($alat);
            $data['foto'] = $this->uploadAndResizeFoto($request->file('foto'));
        }

        $alat->update($data);

        if ($alat->wasChanged('nama_alat')) {
            $this->generateQrCode($alat);
        }

        return redirect()->route('alats.index')
            ->with('success', 'Alat berhasil diperbarui.');
    }

    public function generateAllQrCodes()
    {
        try {
            Alat::chunk(100, function ($alats) {
                foreach ($alats as $alat) {
                    $this->generateQrCode($alat);
                }
            });

            return redirect()->route('alats.index')
                ->with('success', 'Semua QR Code alat berhasil diperbarui/di-generate.');

        } catch (\Exception $e) {
            Log::error('Mass QR Generation failed: ' . $e->getMessage());
            return redirect()->route('alats.index')
                ->with('error', 'Terjadi kesalahan saat generate massal: ' . $e->getMessage());
        }
    }

    public function destroy(string $hashid)
    {
        $alat = $this->resolveHashid($hashid);
        $alat->delete();
        return back()->with('success', 'Alat dipindahkan ke tempat sampah.');
    }

    public function trashed()
    {
        $data = Alat::onlyTrashed()->paginate(10);
        return view('alats.trashed', compact('data'));
    }

    public function restore(string $hashid)
    {
        $alat = $this->resolveHashidWithTrashed($hashid);
        $alat->restore();
        return back()->with('success', 'Alat berhasil dipulihkan.');
    }

    public function forceDelete(string $hashid)
    {
        $alat = $this->resolveHashidWithTrashed($hashid);

        if ($alat->qr_code && Storage::disk('public')->exists($alat->qr_code)) {
            Storage::disk('public')->delete($alat->qr_code);
        }

        $this->deleteFoto($alat);

        $alat->forceDelete();
        return back()->with('success', 'Alat dihapus secara permanen.');
    }

    public function generateQrCode(Alat $alat): void
    {
        Storage::makeDirectory('public/qrcodes', 0755, true);

        $qrCodePath = 'qrcodes/alat_' . $alat->hashid . '_' . Str::slug($alat->nama_alat) . '.png';
        $fullPath = storage_path('app/public/' . $qrCodePath);

        if ($alat->qr_code && Storage::disk('public')->exists($alat->qr_code)) {
            Storage::disk('public')->delete($alat->qr_code);
        }

        try {
            $qrCode = new QrCode(
                data: route('login') . '?alat_id=' . $alat->hashid,
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
        } catch (\Exception $e) {
            Log::error('QR Generation failed for alat ' . $alat->id . ': ' . $e->getMessage());
            throw $e;
        }
    }

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

        $originalPath = 'alat/' . $filename;
        Storage::disk('public')->put(
            $originalPath,
            $image->scaleDown(width: 1200)->toWebp(80)->toFilePointer()
        );

        $thumbPath = 'alat/thumb_' . $filename;
        Storage::disk('public')->put(
            $thumbPath,
            $image->cover(200, 200)->toWebp(60)->toFilePointer()
        );

        return $filename;
    }

    protected function deleteFoto(Alat $alat): void
    {
        if ($alat->foto) {
            Storage::disk('public')->delete('alat/' . $alat->foto);
            Storage::disk('public')->delete('alat/thumb_' . $alat->foto);
        }
    }

public function checkExpired()
{
    Log::info('🔔 [checkExpired] Dimulai pada: ' . now());

    $admins = User::whereIn('role', ['admin', 'super'])->get();
    if ($admins->isEmpty()) {
        Log::warning('⚠️ [checkExpired] Tidak ada admin/super user');
        return;
    }

    $today = now()->startOfDay();
    $warningLimit = now()->addDays(7)->endOfDay();

    // ✅ Ambil alat yang expired atau warning
    $expired = Alat::whereNotNull('masa_berlaku')
        ->whereDate('masa_berlaku', '<', $today->toDateString())
        ->get();

    $warning = Alat::whereNotNull('masa_berlaku')
        ->whereBetween('masa_berlaku', [$today->toDateString(), $warningLimit->toDateString()])
        ->get();

    $alatsToProcess = $expired->merge($warning);

    Log::info("📊 [checkExpired] Total alat to process: {$alatsToProcess->count()}");

    $inserted = 0;
    $skipped = 0;
    $cleared = 0;

    foreach ($alatsToProcess as $alat) {
        $status = $alat->masa_berlaku < $today ? 'expired' : 'warning';

        // ✅ STEP 1: CLEAR notifikasi LAMA untuk alat ini
        // Hapus semua notifikasi lama untuk alat ini dari semua admin
        $deletedCount = DB::table('notifications')
            ->where('type', 'App\Notifications\AlatExpiredNotification')
            ->where(function ($query) use ($alat) {
                $query->where('data->alat_id', $alat->id)
                      ->orWhere('data->alat_hashid', $alat->hashid);
            })
            ->delete();

        if ($deletedCount > 0) {
            Log::info("🧹 [checkExpired] Cleared {$deletedCount} notifikasi lama untuk alat '{$alat->nama_alat}'");
            $cleared += $deletedCount;
        }

        // ✅ STEP 2: Cek last_notified_at (apakah sudah lewat 24 jam?)
        if (!is_null($alat->last_notified_at)) {
            $lastNotified = Carbon::parse($alat->last_notified_at);
            if ($lastNotified->diffInHours(now()) < 24) {
                Log::info("⏭️ [checkExpired] Skipped - alat '{$alat->nama_alat}' sudah dinotifikasi dalam 24 jam terakhir");
                $skipped++;
                continue;  // ← SKIP, tidak kirim notifikasi
            }
        }

        // ✅ STEP 3: KIRIM notifikasi BARU ke semua admin
        foreach ($admins as $admin) {
            try {
                // ✅ Gunakan Notification class
                $admin->notify(new AlatExpiredNotification($alat, $status));
                $inserted++;

                Log::info("✅ [checkExpired] Notifikasi baru terkirim ke user {$admin->id} untuk alat '{$alat->nama_alat}'");
            } catch (\Exception $e) {
                Log::error("❌ [checkExpired] Gagal kirim notifikasi alat '{$alat->nama_alat}' ke user {$admin->id}: " . $e->getMessage());
            }
        }

        // ✅ STEP 4: UPDATE last_notified_at = sekarang
        $alat->update(['last_notified_at' => now()]);
    }

    Log::info("📊 [checkExpired] Selesai - Inserted: {$inserted}, Skipped: {$skipped}, Cleared: {$cleared}");
}

    public function exportExcel()
    {
        return Excel::download(new AlatExport, 'alat.xlsx');
    }
}
