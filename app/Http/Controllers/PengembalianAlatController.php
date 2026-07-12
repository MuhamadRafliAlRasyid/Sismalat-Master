<?php

namespace App\Http\Controllers;

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
use Illuminate\Support\Str;

// ✨ IMPORT UNTUK INTERVENTION IMAGE v4 (BUKAN Facade!)
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;

class PengembalianAlatController extends Controller
{
    protected function resolveHashid($hashid)
    {
        $id = app(HashIdService::class)->decode($hashid);
        if (!$id) abort(404);
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
        return view('pengembalian_alat.index', compact('data'));
    }

    /* ================= CREATE ================= */
    public function create($hashid)
    {
        $pengambilan = PengambilanAlat::with(['alat', 'pengembalians'])
            ->findOrFail($this->resolveHashid($hashid));

        return view('pengembalian_alat.create', compact('pengambilan'));
    }

    /* ================= STORE ================= */
    public function store(Request $request, $hashid)
    {
        try {
            Log::info('=== STORE PENGEMBALIAN START ===', [
                'hashid' => $hashid,
                'request' => $request->all(),
            ]);

            $pengambilan = PengambilanAlat::with('pengembalians')
                ->findOrFail($this->resolveHashid($hashid));

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

            $validated = $request->validate($rules, [
                'keterangan.required' => 'Keterangan wajib diisi karena pengembalian melewati tenggat waktu.',
                'keterangan.min' => 'Keterangan minimal 10 karakter.',
                'foto.required' => 'Foto bukti wajib diupload karena pengembalian melewati tenggat waktu.',
            ]);

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

            DB::transaction(function () use ($pengambilan, $dataToInsert, $sisaPinjaman) {
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

            return redirect()->route('pengambilan_alat.show', $pengambilan->hashid)
                ->with('success', 'Pengembalian berhasil disimpan.');

        } catch (\Exception $e) {
            Log::error('=== STORE PENGEMBALIAN ERROR ===', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return back()
                ->withInput()
                ->with('error', 'Gagal menyimpan pengembalian: ' . $e->getMessage());
        }
    }

    /* ================= SHOW ================= */
    public function show($hashid)
    {
        $data = PengembalianAlat::with(['pengambilan.alat', 'user'])
            ->findOrFail($this->resolveHashid($hashid));

        if (Auth::user()->role !== 'admin' && Auth::id() !== $data->user_id) {
            abort(403);
        }

        return view('pengembalian_alat.show', compact('data'));
    }

    /* ================= EDIT ================= */
    public function edit($hashid)
    {
        $data = PengembalianAlat::with(['pengambilan.alat', 'pengambilan.pengembalians'])
            ->findOrFail($this->resolveHashid($hashid));

        return view('pengembalian_alat.edit', compact('data'));
    }

    /* ================= UPDATE ================= */
    public function update(Request $request, $hashid)
    {
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

        $validated = $request->validate($rules);

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

        return redirect()->route('pengembalian_alat.index')
            ->with('success', 'Data pengembalian berhasil diperbarui.');
    }

    /* ================= DESTROY ================= */
    public function destroy($hashid)
    {
        $data = PengembalianAlat::with('pengambilan')->findOrFail($this->resolveHashid($hashid));

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

        return back()->with('success', 'Data pengembalian dihapus.');
    }

    /* ================= EXPORT PDF ================= */
    public function exportPdf($hashid = null)
    {
        if ($hashid) {
            $data = PengembalianAlat::with(['user', 'pengambilan.alat'])
                ->findOrFail($this->resolveHashid($hashid));
            $list = collect([$data]);
        } else {
            $list = PengembalianAlat::with(['user', 'pengambilan.alat'])->get();
        }

        $pdf = Pdf::loadView('pengembalian_alat.export-pdf', compact('list'));
        return $pdf->download('pengembalian_alat.pdf');
    }

    /* ================= FOTO HELPER (v4 COMPATIBLE) ================= */

    /**
     * ✨ Upload foto dengan Intervention Image v4
     *
     * Perbedaan v4 vs v2:
     * - v2: Image::make($file) → $image->resize()->encode('webp')
     * - v4: $manager->read($file) → $image->scaleDown()->toWebp()
     *
     * PENTING:
     * - JANGAN gunakan Image::make() atau Facade
     * - Gunakan ImageManager dengan GdDriver
     */
    protected function uploadFotoTransaksi($file, string $folder): string
{
    try {
        // Generate nama file unik
        $filename = date('YmdHis') . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
        $thumbFilename = 'thumb_' . $filename;

        // Simpan file original
        Storage::disk('public')->putFileAs(
            $folder,
            $file,
            $filename
        );

        // Simpan file thumbnail (copy dari original)
        Storage::disk('public')->putFileAs(
            $folder,
            $file,
            $thumbFilename
        );

        Log::info('Foto berhasil diupload (tanpa resize)', [
            'folder' => $folder,
            'filename' => $filename,
            'thumb' => $thumbFilename,
            'size' => $file->getSize(),
        ]);

        return $filename;

    } catch (\Exception $e) {
        Log::error('Gagal upload foto: ' . $e->getMessage(), [
            'trace' => $e->getTraceAsString(),
        ]);

        throw $e;
    }
}

    protected function deleteFotoTransaksi($filename, string $folder): void
    {
        if ($filename) {
            Storage::disk('public')->delete("$folder/$filename");
            Storage::disk('public')->delete("$folder/thumb_$filename");
        }
    }
}
