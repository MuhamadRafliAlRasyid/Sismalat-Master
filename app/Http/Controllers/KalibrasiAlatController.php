<?php

namespace App\Http\Controllers;

use App\Models\Alat;
use App\Models\KalibrasiAlat;
use App\Services\HashIdService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class KalibrasiAlatController extends Controller
{
    protected function decode($hashid)
    {
        $id = app(HashIdService::class)->decode($hashid);
        if (!$id) abort(404);
        return $id;
    }

    /* ================= INDEX ================= */

    public function index(Request $request)
    {
        $query = KalibrasiAlat::with('alat');

        // 🔍 search
        if ($request->search) {
            $query->whereHas('alat', function ($q) use ($request) {
                $q->where('nama_alat', 'like', '%' . $request->search . '%');
            });
        }

        $data = $query->latest()->paginate(10)->withQueryString();

        return view('kalibrasis.index', compact('data'));
    }

    /* ================= CREATE ================= */

    public function create($hashid)
    {
        $alat = Alat::findOrFail($this->decode($hashid));

        return view('kalibrasis.create', compact('alat'));
    }

    /* ================= STORE ================= */



public function store(Request $request, $hashid)
    {
        $alat = Alat::findOrFail($this->decode($hashid));

        $validated = $request->validate([
            'tanggal_kalibrasi' => 'required|date|before_or_equal:today',
            'masa_berlaku_baru' => 'required|date',
            'no_sertifikat' => 'nullable|string|max:255',
            'keterangan' => 'nullable|string',
        ]);

        $tanggalKalibrasi = Carbon::parse($validated['tanggal_kalibrasi']);
        $masaBerlakuBaru = Carbon::parse($validated['masa_berlaku_baru']);

        $lastKalibrasi = KalibrasiAlat::where('alat_id', $alat->id)
            ->latest('tanggal_kalibrasi')
            ->first();

        if ($lastKalibrasi && $tanggalKalibrasi->lt($lastKalibrasi->tanggal_kalibrasi)) {
            return back()->withErrors([
                'tanggal_kalibrasi' => 'Tanggal tidak boleh lebih lama dari sebelumnya'
            ])->withInput();
        }

        if ($masaBerlakuBaru->lte($tanggalKalibrasi)) {
            return back()->withErrors([
                'masa_berlaku_baru' => 'Masa berlaku harus setelah tanggal kalibrasi'
            ])->withInput();
        }

        if ($lastKalibrasi && $masaBerlakuBaru->lte($lastKalibrasi->masa_berlaku_baru)) {
            return back()->withErrors([
                'masa_berlaku_baru' => 'Harus lebih besar dari masa berlaku sebelumnya'
            ])->withInput();
        }

        $kalibrasi = KalibrasiAlat::create([
            'alat_id' => $alat->id,
            ...$validated
        ]);

        $alat->update([
            'masa_berlaku' => $validated['masa_berlaku_baru'],
            'last_notified_at' => null
        ]);

        // ✅ Tandai notifikasi lama sebagai sudah dibaca
        DB::table('notifications')
            ->where('data->alat_id', $alat->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return redirect()->route('kalibrasis.show', $kalibrasi->hashid)
            ->with('success', 'Kalibrasi berhasil disimpan');
    }


    /* ================= SHOW ================= */

    public function show($hashid)
    {
        $id = app(HashIdService::class)->decode($hashid);
        abort_if(!$id, 404);

        $data = KalibrasiAlat::with('alat')->findOrFail($id);
        // $data sekarang berisi satu record kalibrasi beserta relasi alatnya

        return view('kalibrasis.show', compact('data'));
    }

    /* ================= EDIT ================= */

    public function edit($hashid)
    {
        $data = KalibrasiAlat::with('alat')
            ->findOrFail($this->decode($hashid));

        return view('kalibrasis.edit', compact('data'));
    }

    /* ================= UPDATE ================= */

    public function update(Request $request, $hashid)
    {
        $data = KalibrasiAlat::with('alat')->findOrFail($this->decode($hashid));
        $alat = $data->alat;

        $validated = $request->validate([
            'tanggal_kalibrasi' => 'required|date|before_or_equal:today',
            'masa_berlaku_baru' => 'required|date',
            'no_sertifikat' => 'nullable|string|max:255',
            'keterangan' => 'nullable|string',
        ]);

        $tanggalKalibrasi = Carbon::parse($validated['tanggal_kalibrasi']);
        $masaBerlakuBaru = Carbon::parse($validated['masa_berlaku_baru']);

        $lastKalibrasi = KalibrasiAlat::where('alat_id', $alat->id)
            ->where('id', '!=', $data->id)
            ->latest('tanggal_kalibrasi')
            ->first();

        if ($lastKalibrasi && $tanggalKalibrasi->lt($lastKalibrasi->tanggal_kalibrasi)) {
            return back()->withErrors([
                'tanggal_kalibrasi' => 'Tanggal tidak boleh lebih lama dari data lain'
            ])->withInput();
        }

        if ($masaBerlakuBaru->lte($tanggalKalibrasi)) {
            return back()->withErrors([
                'masa_berlaku_baru' => 'Masa berlaku harus setelah tanggal kalibrasi'
            ])->withInput();
        }

        if ($lastKalibrasi && $masaBerlakuBaru->lte($lastKalibrasi->masa_berlaku_baru)) {
            return back()->withErrors([
                'masa_berlaku_baru' => 'Harus lebih besar dari data sebelumnya'
            ])->withInput();
        }

        $data->update($validated);

        $latest = KalibrasiAlat::where('alat_id', $alat->id)
            ->latest('tanggal_kalibrasi')
            ->first();

        if ($latest && $latest->id == $data->id) {
            $alat->update([
                'masa_berlaku' => $validated['masa_berlaku_baru']
            ]);

            // ✅ Tandai notifikasi lama sebagai sudah dibaca
            DB::table('notifications')
                ->where('data->alat_id', $alat->id)
                ->whereNull('read_at')
                ->update(['read_at' => now()]);
        }

        return redirect()->route('kalibrasis.show', $data->hashid)
            ->with('success', 'Kalibrasi berhasil diupdate');
    }

    /* ================= DELETE ================= */

    public function destroy($hashid)
    {
        $data = KalibrasiAlat::findOrFail($this->decode($hashid));
        $alat = $data->alat;

        $data->delete();

        // 🔥 recalc masa berlaku dari data terbaru
        $latest = KalibrasiAlat::where('alat_id', $alat->id)
            ->latest('tanggal_kalibrasi')
            ->first();

        if ($latest) {
            $alat->update([
                'masa_berlaku' => $latest->masa_berlaku_baru
            ]);
        } else {
            $alat->update([
                'masa_berlaku' => null
            ]);
        }

        return redirect()->route('kalibrasis.index')
            ->with('success', 'Data kalibrasi dihapus');
    }
}
