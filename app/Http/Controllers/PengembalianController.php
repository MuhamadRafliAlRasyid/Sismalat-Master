<?php

namespace App\Http\Controllers;

use App\Models\Bagian;
use App\Models\Pengambilan;
use App\Models\Pengembalian;
use App\Models\Spareparts;
use App\Models\User;
use App\Services\HashIdService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class PengembalianController extends Controller
{
    protected function resolveHashid($hashid)
    {
        $id = app(HashIdService::class)->decode($hashid);
        if (!$id) abort(404, 'Data tidak ditemukan');
        return $id;
    }

    /* -------------------------------------------------------------------------- */
    /*                                    INDEX                                   */
    /* -------------------------------------------------------------------------- */
    public function index(Request $request)
    {
        $query = Pengembalian::with(['user', 'sparepart', 'pengambilan']);

        if (Auth::user()->role !== 'admin') {
            $query->where('user_id', Auth::user()->id);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('alasan', 'like', "%{$search}%")
                  ->orWhere('keterangan', 'like', "%{$search}%")
                  ->orWhereHas('sparepart', fn($q) => $q->where('nama_part', 'like', "%{$search}%"))
                  ->orWhereHas('user', fn($q) => $q->where('name', 'like', "%{$search}%"));
            });
        }

        $pengembalians = $query->latest()->paginate(10)->withQueryString();

        return view('pengembalian.index', compact('pengembalians'));
    }

    /* -------------------------------------------------------------------------- */
    /*                                   CREATE                                   */
    /* -------------------------------------------------------------------------- */
    public function create()
{
    // Ambil pengambilan yang masih memiliki sisa untuk dikembalikan
    $pengambilans = Pengambilan::with(['sparepart', 'user'])
        ->get()
        ->filter(function ($pengambilan) {
            $sudahDikembalikan = $pengambilan->pengembalian()->sum('jumlah_dikembalikan') ?? 0;
            return $pengambilan->jumlah > $sudahDikembalikan;
        });

    // Kirim variabel yang dibutuhkan view
    $users = User::all();           // ← Tambahkan ini
    $bagians = Bagian::all();       // ← Tambahkan ini jika view membutuhkannya

    return view('pengembalian.create', compact('pengambilans', 'users', 'bagians'));
}
    /* -------------------------------------------------------------------------- */
    /*                                    STORE                                   */
    /* -------------------------------------------------------------------------- */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'pengambilan_id'      => 'required|exists:pengambilan_spareparts,id',
            'jumlah_dikembalikan' => 'required|integer|min:1',
            'kondisi'             => 'required|in:baik,rusak',
            'alasan'              => 'required|string|max:255',
            'keterangan'          => 'nullable|string',
        ]);

        $pengambilan = Pengambilan::findOrFail($validated['pengambilan_id']);
        $sparepart   = $pengambilan->sparepart;

        // Cek sisa yang boleh dikembalikan
        $sudahDikembalikan = $pengambilan->pengembalian()->sum('jumlah_dikembalikan') ?? 0;
        $sisa = $pengambilan->jumlah - $sudahDikembalikan;

        if ($validated['jumlah_dikembalikan'] > $sisa) {
            return back()->with('error', 'Jumlah dikembalikan melebihi sisa pengambilan.');
        }

        // Update stok sparepart
        if ($validated['kondisi'] === 'baik') {
            $sparepart->increment('jumlah_baru', $validated['jumlah_dikembalikan']);
        } else {
            $sparepart->increment('jumlah_bekas', $validated['jumlah_dikembalikan']);
        }

        Pengembalian::create([
            'pengambilan_id'      => $validated['pengambilan_id'],
            'sparepart_id'        => $pengambilan->spareparts_id,
            'user_id'             => Auth::id(),
            'jumlah_dikembalikan' => $validated['jumlah_dikembalikan'],
            'kondisi'             => $validated['kondisi'],
            'alasan'              => $validated['alasan'],
            'keterangan'          => $validated['keterangan'],
            'tanggal_kembali'     => now(),
        ]);

        return redirect()->route('pengembalian.index')
            ->with('success', 'Pengembalian sparepart berhasil dicatat.');
    }

    /* -------------------------------------------------------------------------- */
    /*                                     SHOW                                   */
    /* -------------------------------------------------------------------------- */
    public function show($hashid)
    {
        $pengembalian = Pengembalian::with(['user', 'sparepart', 'pengambilan'])
            ->findOrFail($this->resolveHashid($hashid));

        if (Auth::user()->role !== 'admin' && Auth::user()->id !== $pengembalian->user_id) {
            abort(403);
        }

        return view('pengembalian.show', compact('pengembalian'));
    }

    /* -------------------------------------------------------------------------- */
    /*                                     EDIT                                   */
    /* -------------------------------------------------------------------------- */
    public function edit($hashid)
{
    $pengembalian = Pengembalian::with(['pengambilan', 'sparepart', 'user'])
        ->findOrFail($this->resolveHashid($hashid));

    if (Auth::user()->role !== 'admin' && Auth::user()->id !== $pengembalian->user_id) {
        abort(403);
    }

    // Ambil semua pengambilan untuk pilihan edit
    $pengambilans = Pengambilan::with(['sparepart', 'user'])->get();

    return view('pengembalian.edit', compact('pengembalian', 'pengambilans'));
}

    /* -------------------------------------------------------------------------- */
    /*                                    UPDATE                                  */
    /* -------------------------------------------------------------------------- */
    public function update(Request $request, $hashid)
    {
        $pengembalian = Pengembalian::findOrFail($this->resolveHashid($hashid));

        if (Auth::user()->role !== 'admin' && Auth::user()->id !== $pengembalian->user_id) {
            abort(403);
        }

        $validated = $request->validate([
            'pengambilan_id'      => 'required|exists:pengambilan_spareparts,id',
            'jumlah_dikembalikan' => 'required|integer|min:1',
            'kondisi'             => 'required|in:baik,rusak',
            'alasan'              => 'required|string|max:255',
            'keterangan'          => 'nullable|string',
        ]);

        // Kembalikan stok lama
        if ($pengembalian->kondisi === 'baik') {
            $pengembalian->sparepart->increment('jumlah_baru', $pengembalian->jumlah_dikembalikan);
        } else {
            $pengembalian->sparepart->increment('jumlah_bekas', $pengembalian->jumlah_dikembalikan);
        }

        // Update stok baru
        if ($validated['kondisi'] === 'baik') {
            $pengembalian->sparepart->decrement('jumlah_baru', $validated['jumlah_dikembalikan']);
        } else {
            $pengembalian->sparepart->decrement('jumlah_bekas', $validated['jumlah_dikembalikan']);
        }

        $pengembalian->update($validated);

        return redirect()->route('pengembalian.show', $pengembalian->hashid)
            ->with('success', 'Pengembalian berhasil diperbarui.');
    }

    /* -------------------------------------------------------------------------- */
    /*                                   DESTROY                                  */
    /* -------------------------------------------------------------------------- */
    public function destroy($hashid)
    {
        $pengembalian = Pengembalian::findOrFail($this->resolveHashid($hashid));

        if (Auth::user()->role !== 'admin' && Auth::user()->id !== $pengembalian->user_id) {
            abort(403);
        }

        // Kembalikan stok
        if ($pengembalian->kondisi === 'baik') {
            $pengembalian->sparepart->increment('jumlah_baru', $pengembalian->jumlah_dikembalikan);
        } else {
            $pengembalian->sparepart->increment('jumlah_bekas', $pengembalian->jumlah_dikembalikan);
        }

        $pengembalian->delete();

        return redirect()->route('pengembalian.index')
            ->with('success', 'Pengembalian berhasil dihapus.');
    }
}
