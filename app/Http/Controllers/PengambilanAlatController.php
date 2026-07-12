<?php

namespace App\Http\Controllers;

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
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;
use Intervention\Image\ImageManager;

class PengambilanAlatController extends Controller
{
    protected function resolveHashid($hashid)
    {
        $id = app(HashIdService::class)->decode($hashid);
        if (!$id) abort(404);
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

        // ✨ Jalankan pengecekan notifikasi warning
        $this->checkPeminjamanWarning();

        $data = $query->latest()->paginate(10)->withQueryString();
        return view('pengambilan_alat.index', compact('data'));
    }

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

        return view('pengambilan_alat.create', compact(
            'users',
            'bagians',
            'alatsGrouped',
            'selectedHashid'
        ));
    }

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

            $validated = $request->validate([
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

            if ($request->hasFile('foto')) {
                $validated['foto'] = $this->uploadFotoTransaksi($request->file('foto'), 'pengambilan');
            }

            DB::transaction(function () use ($validated) {
                $alatId = $this->resolveHashid($validated['alat_id']);
                $alat = Alat::findOrFail($alatId);

                if ($alat->jumlah < $validated['jumlah']) {
                    throw new \Exception('Stok alat tidak mencukupi');
                }

                $alat->decrement('jumlah', $validated['jumlah']);

                $validated['alat_id'] = $alatId;
                $validated['status']  = 'dipinjam';

                PengambilanAlat::create($validated);
            });

            return redirect()->route('pengambilan_alat.index')
                ->with('success', 'Pengambilan alat berhasil ditambahkan.');
        } catch (\Throwable $e) {
            Log::error('ERROR STORE PENGAMBILAN ALAT', [
                'message' => $e->getMessage(),
                'line'    => $e->getLine(),
                'file'    => $e->getFile()
            ]);
            return back()->withInput()->with('error', 'Terjadi error: ' . $e->getMessage());
        }
    }

    public function show($hashid)
    {
        $data = PengambilanAlat::with(['user', 'bagian', 'alat'])
            ->findOrFail($this->resolveHashid($hashid));

        if (Auth::user()->role !== 'admin' && Auth::id() !== $data->user_id) {
            abort(403);
        }

        return view('pengambilan_alat.show', compact('data'));
    }

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
        return $pdf->download('pengambilan_alat.pdf');
    }

    public function edit($hashid)
    {
        $data = PengambilanAlat::findOrFail($this->resolveHashid($hashid));

        if (Auth::user()->role !== 'admin' && Auth::id() !== $data->user_id) {
            abort(403);
        }

        $users   = User::all();
        $bagians = Bagian::all();
        $alatsGrouped = $this->groupAlatsForForm(Alat::whereNull('deleted_at')->get());
        $selectedHashid = $data->alat ? $data->alat->hashid : '';

        return view('pengambilan_alat.edit', compact(
            'data', 'users', 'bagians', 'alatsGrouped', 'selectedHashid'
        ));
    }

    public function update(Request $request, $hashid)
    {
        $data = PengambilanAlat::findOrFail($this->resolveHashid($hashid));

        if (Auth::user()->role !== 'admin' && Auth::id() !== $data->user_id) {
            abort(403);
        }

        $validated = $request->validate([
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

        return redirect()->route('pengambilan_alat.show', $data->hashid)
            ->with('success', 'Data berhasil diperbarui');
    }

    public function destroy($hashid)
    {
        $data = PengambilanAlat::findOrFail($this->resolveHashid($hashid));

        if (Auth::user()->role !== 'admin' && Auth::id() !== $data->user_id) {
            abort(403);
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

        return redirect()->route('pengambilan_alat.index')
            ->with('success', 'Data berhasil dihapus');
    }

    /**
     * ✨ CEK PEMINJAMAN WARNING - HANYA 1 METHOD (versi final)
     *
     * Logika:
     * 1. PRIORITAS 1: Warning 1 Hari (URGENT) - sisa ≤ 1 hari
     * 2. PRIORITAS 2: Warning 15% - sisa ≤ 15% dari lama pinjam
     */
    public function checkPeminjamanWarning(): void
    {
        try {
            Log::info('checkPeminjamanWarning dijalankan pada: ' . now());

            // Ambil semua peminjaman yang masih aktif
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
                    // Skip jika user tidak ada
                    if (!$pengambilan->user) {
                        $skipped++;
                        continue;
                    }

                    $shouldSendNotification = false;
                    $warningType = null;

                    // ✨ PRIORITAS 1: Warning 1 Hari (URGENT)
                    if ($pengambilan->should_warn_1day) {
                        $shouldSendNotification = true;
                        $warningType = '1_day';
                    }
                    // ✨ PRIORITAS 2: Warning 15%
                    elseif ($pengambilan->should_warn_15) {
                        $shouldSendNotification = true;
                        $warningType = '15_percent';
                    }

                    if (!$shouldSendNotification) {
                        $skipped++;
                        continue;
                    }

                    // Kirim notifikasi
                    $pengambilan->user->notify(new PeminjamanWarningNotification($pengambilan, $warningType));

                    // Update tracking berdasarkan jenis warning
                    if ($warningType === '1_day') {
                        $pengambilan->markWarning1DaySent();
                        $notified1Day++;
                        Log::info("🔴 Notifikasi 1 HARI dikirim", [
                            'user_id' => $pengambilan->user->id,
                            'pengambilan_id' => $pengambilan->id,
                            'alat' => $pengambilan->alat->nama_alat,
                            'sisa_hari' => $pengambilan->sisa_hari,
                        ]);
                    } else {
                        $pengambilan->markWarning15Sent();
                        $notified15Percent++;
                        Log::info("⚠️ Notifikasi 15% dikirim", [
                            'user_id' => $pengambilan->user->id,
                            'pengambilan_id' => $pengambilan->id,
                            'alat' => $pengambilan->alat->nama_alat,
                            'sisa_hari' => $pengambilan->sisa_hari,
                            'persentase' => $pengambilan->percentase_sisa,
                        ]);
                    }

                } catch (\Exception $e) {
                    $errors++;
                    Log::error("Gagal kirim notifikasi untuk pengambilan {$pengambilan->id}: " . $e->getMessage());
                }
            }

            $totalNotified = $notified1Day + $notified15Percent;
            Log::info("CheckPeminjamanWarning selesai. Total: {$totalNotified} terkirim ({$notified1Day} urgent, {$notified15Percent} warning), {$skipped} dilewati, {$errors} error");

        } catch (\Exception $e) {
            Log::error('Error di checkPeminjamanWarning: ' . $e->getMessage());
        }
    }

    protected function uploadFotoTransaksi($file, string $folder): string
    {
        if (!class_exists(GdDriver::class)) {
            $filename = date('YmdHis') . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
            Storage::disk('public')->putFileAs($folder, $file, $filename);
            Storage::disk('public')->putFileAs($folder . '/thumb', $file, $filename);
            return $filename;
        }

        $manager = new ImageManager(new GdDriver());
        $image = $manager->read($file);
        $filename = date('YmdHis') . '_' . Str::random(10) . '.webp';

        Storage::disk('public')->put(
            "$folder/$filename",
            $image->scaleDown(width: 1200)->toWebp(80)->toFilePointer()
        );

        Storage::disk('public')->put(
            "$folder/thumb_$filename",
            $image->cover(200, 200)->toWebp(60)->toFilePointer()
        );

        return $filename;
    }

    protected function deleteFotoTransaksi($filename, string $folder): void
    {
        if ($filename) {
            Storage::disk('public')->delete("$folder/$filename");
            Storage::disk('public')->delete("$folder/thumb_$filename");
        }
    }
}
