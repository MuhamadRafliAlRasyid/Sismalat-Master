<?php

namespace App\Http\Controllers;

use App\Models\Alat;
use App\Models\Bagian;
use App\Models\Pengambilan;
use App\Models\Spareparts;
use App\Models\User;
use App\Services\HashIdService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class PengambilanSparepartController extends Controller
{
    /* -------------------------------------------------------------------------- */
    /*                              HASHID RESOLVER                               */
    /* -------------------------------------------------------------------------- */

    protected function resolveHashid($hashid)
    {
        $id = app(HashIdService::class)->decode($hashid);
        if (!$id) abort(404);
        return $id;
    }

    protected function resolveSparepartHash($hashid)
    {
        $id = app(HashIdService::class)->decode($hashid);
        if (!$id) abort(404);
        return $id;
    }

    /* -------------------------------------------------------------------------- */
    /*                                    INDEX                                   */
    /* -------------------------------------------------------------------------- */

    public function index(Request $request)
    {
        $query = Pengambilan::with(['user', 'bagian', 'sparepart']);

        if (Auth::check()) {
            if (Auth::user()->role !== 'admin') {
                $query->where('user_id', Auth::user()->id);
            }
        }

        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('waktu_pengambilan', 'like', "%{$search}%")
                    ->orWhere('jumlah', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    })
                    ->orWhereHas('bagian', function ($q) use ($search) {
                        $q->where('nama', 'like', "%{$search}%");
                    })
                    ->orWhereHas('sparepart', function ($q) use ($search) {
                        $q->where('nama_part', 'like', "%{$search}%")
                            ->orWhere('model', 'like', "%{$search}%");
                    });
            });
        }

        $pengambilanSpareparts = $query->paginate(10)->withQueryString();
        Log::info('Index Pengambilan Spareparts: ', $pengambilanSpareparts->toArray());

        return view('pengambilan.index', compact('pengambilanSpareparts'));
    }

    /* -------------------------------------------------------------------------- */
    /*                                   CREATE                                   */
    /* -------------------------------------------------------------------------- */

    /**
     * Menampilkan form pengambilan sparepart.
     * Parameter $spareparts_hashid opsional untuk preselected sparepart dari QR.
     */
    public function create(Request $request, $spareparts_hashid = null)
    {
        $users = User::all();
        $bagians = Bagian::all();
        $spareparts = Spareparts::all();

        $selectedSparepart = null;
        $selectedHashid = null;

        // Jika ada parameter di URL (dari route create.with-sparepart)
        if ($spareparts_hashid) {
            $id = $this->resolveSparepartHash($spareparts_hashid);
            $selectedSparepart = Spareparts::find($id);
            $selectedHashid = $spareparts_hashid;
        }
        // Jika ada query string ?spareparts_hashid=... (dari QR scan atau redirect)
        elseif ($request->has('spareparts_hashid')) {
            $hash = $request->get('spareparts_hashid');
            $id = $this->resolveSparepartHash($hash);
            if ($id) {
                $selectedSparepart = Spareparts::find($id);
                $selectedHashid = $hash;
            }
        }

        // Filter user & bagian untuk non-admin
        if (Auth::user()->role !== 'admin') {
            $users = User::where('id', Auth::id())->get();
            $bagians = Auth::user()->bagian
                ? Bagian::where('id', Auth::user()->bagian->id)->get()
                : collect();
        }

        return view('pengambilan.create', compact(
            'users',
            'bagians',
            'spareparts',
            'selectedSparepart',
            'selectedHashid'
        ));
    }

    /* -------------------------------------------------------------------------- */
    /*                                    STORE                                   */
    /* -------------------------------------------------------------------------- */

    public function store(Request $request)
    {
        if (Auth::user()->role !== 'admin') {
            $request->merge([
                'user_id' => Auth::user()->id,
                'bagian_id' => Auth::user()->bagian_id ?? 1,
            ]);
        }

        Log::info('Store Request: ', $request->all());

        try {
            $validated = $request->validate([
                'user_id'        => 'required|exists:users,id',
                'bagian_id'      => 'required|exists:bagian,id',
                'spareparts_id'  => 'required', // akan berisi hashid dari form
                'jumlah'         => 'required|integer|min:1',
                'satuan'         => 'required|string|max:50',
                'keperluan'      => 'required|string|max:255',
                'waktu_pengambilan' => 'required|date',
            ]);

            // Decode sparepart hashid
            $sparepartId = $this->resolveSparepartHash($request->spareparts_id);
            $sparepart = Spareparts::findOrFail($sparepartId);

            $jumlah = $request->jumlah;

            // Logika stok berdasarkan part_type
            if ($request->part_type === 'baru') {
                if ($sparepart->jumlah_baru < $jumlah) {
                    return back()->with('error', 'Stok baru tidak mencukupi.');
                }
                $sparepart->decrement('jumlah_baru', $jumlah);
            } elseif ($request->part_type === 'bekas') {
                if ($sparepart->jumlah_bekas < $jumlah) {
                    return back()->with('error', 'Stok bekas tidak mencukupi.');
                }
                $sparepart->decrement('jumlah_bekas', $jumlah);
            }

            $validated['spareparts_id'] = $sparepartId;

            $pengambilanSparepart = Pengambilan::create($validated);

            return redirect()->route('pengambilan.show', $pengambilanSparepart->hashid)
                ->with('success', 'Pengambilan sparepart berhasil ditambahkan.');
        } catch (\Exception $e) {
            Log::error('Error di store Pengambilan: ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /* -------------------------------------------------------------------------- */
    /*                                     SHOW                                   */
    /* -------------------------------------------------------------------------- */

    public function show($hashid)
    {
        $pengambilanSparepart = Pengambilan::findOrFail(
            $this->resolveHashid($hashid)
        );

        $pengambilanSparepart->load(['user', 'bagian', 'sparepart']);

        if (Auth::user()->role !== 'admin' && Auth::user()->id !== $pengambilanSparepart->user_id) {
            abort(403);
        }

        return view('pengambilan.show', compact('pengambilanSparepart'));
    }

    /* -------------------------------------------------------------------------- */
    /*                                  EXPORT PDF                                */
    /* -------------------------------------------------------------------------- */

    public function exportPdf($hashid = null)
    {
        if ($hashid) {
            $decodedId = $this->resolveHashid($hashid);
            $pengambilanSparepart = Pengambilan::with(['user', 'sparepart'])
                ->findOrFail($decodedId);

            $pengambilanSpareparts = collect([$pengambilanSparepart]);
        } else {
            $pengambilanSpareparts = Pengambilan::with(['user', 'sparepart'])->get();
        }
        $pdf = Pdf::loadView('pengambilan.export-pdf', compact('pengambilanSpareparts'))
            ->setPaper('a4', 'portrait')
            ->setOptions([
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
                'defaultFont' => 'DejaVu Sans',
            ]);
        return $pdf->download('pengambilan_report_' . ($hashid ? $hashid : 'all') . '.pdf');
    }

    /* -------------------------------------------------------------------------- */
    /*                                     EDIT                                   */
    /* -------------------------------------------------------------------------- */

    public function edit($hashid)
    {
        $decodedId = $this->resolveHashid($hashid);

        $pengambilanSparepart = Pengambilan::with(['user', 'bagian', 'sparepart'])
            ->findOrFail($decodedId);

        if (Auth::user()->role !== 'admin' && Auth::user()->id !== $pengambilanSparepart->user_id) {
            abort(403);
        }

        $users = User::all();
        $bagians = Bagian::all();
        $spareparts = Spareparts::all();

        if (Auth::user()->role !== 'admin') {
            $users = User::where('id', Auth::user()->id)->get();
            $bagians = Auth::user()->bagian
                ? Bagian::where('id', Auth::user()->bagian->id)->get()
                : collect();
        }

        return view('pengambilan.edit', compact(
            'pengambilanSparepart', 'users', 'bagians', 'spareparts'
        ));
    }

    /* -------------------------------------------------------------------------- */
    /*                                    UPDATE                                  */
    /* -------------------------------------------------------------------------- */

    public function update(Request $request, $hashid)
    {
        $decodedId = $this->resolveHashid($hashid);
        $pengambilanSparepart = Pengambilan::findOrFail($decodedId);
        if (Auth::user()->role !== 'admin' && Auth::user()->id !== $pengambilanSparepart->user_id) {
            abort(403);
        }
        if (Auth::user()->role !== 'admin') {
            $request->merge([
                'user_id' => $pengambilanSparepart->user_id,
                'bagian_id' => $pengambilanSparepart->bagian_id,
            ]);
        }
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'bagian_id' => 'required|exists:bagian,id',
            'spareparts_id' => 'required',
            'part_type' => 'required|in:baru,bekas',
            'jumlah' => 'required|integer|min:1',
            'satuan' => 'required|string|max:50',
            'keperluan' => 'required|string|max:255',
            'waktu_pengambilan' => 'required|date',
        ]);
        $sparepartId = $this->resolveSparepartHash($request->spareparts_id);
        $sparepart = Spareparts::findOrFail($sparepartId);
        $oldJumlah = $pengambilanSparepart->jumlah;
        $newJumlah = $request->jumlah;
        if ($pengambilanSparepart->part_type === 'baru') {
            $sparepart->increment('jumlah_baru', $oldJumlah);
        } else {
            $sparepart->increment('jumlah_bekas', $oldJumlah);
        }
        if ($request->part_type === 'baru') {
            if ($sparepart->jumlah_baru < $newJumlah) {
                return back()->with('error', 'Stok baru tidak cukup.');
            }
            $sparepart->decrement('jumlah_baru', $newJumlah);
        } else {
            if ($sparepart->jumlah_bekas < $newJumlah) {
                return back()->with('error', 'Stok bekas tidak cukup.');
            }
            $sparepart->decrement('jumlah_bekas', $newJumlah);
        }
        $validated['spareparts_id'] = $sparepartId;
        $pengambilanSparepart->update($validated);
        return redirect()->route('pengambilan.show', $pengambilanSparepart->hashid)
            ->with('success', 'Berhasil diperbarui.');
    }

    /* -------------------------------------------------------------------------- */
    /*                                   DESTROY                                  */
    /* -------------------------------------------------------------------------- */

    public function destroy($hashid)
    {
        $decodedId = $this->resolveHashid($hashid);

        $pengambilanSparepart = Pengambilan::findOrFail($decodedId);

        if (Auth::user()->role !== 'admin' && Auth::user()->id !== $pengambilanSparepart->user_id) {
            abort(403);
        }

        $sparepart = $pengambilanSparepart->sparepart;
        $jumlah = $pengambilanSparepart->jumlah;

        if ($pengambilanSparepart->part_type === 'baru') {
            $sparepart->increment('jumlah_baru', $jumlah);
        } else {
            $sparepart->increment('jumlah_bekas', $jumlah);
        }

        $pengambilanSparepart->delete();

        return redirect()->route('pengambilan.index')
            ->with('success', 'Pengambilan sparepart berhasil dihapus.');
    }
}
