<?php

namespace App\Http\Controllers;

use App\Models\Kategori;
use Illuminate\Http\Request;
use App\Services\HashIdService;

class KategoriController extends Controller
{
    public function index(Request $request)
{
    $query = Kategori::query();

    // 🔍 SEARCH
    if ($request->filled('search')) {
        $search = $request->search;

        $query->where(function ($q) use ($search) {
            $q->where('nama', 'like', '%' . $search . '%')
              ->orWhere('keterangan', 'like', '%' . $search . '%');
        });
    }

    $data = $query->latest()
        ->paginate(10)
        ->withQueryString(); // 🔥 biar search tetap saat pagination

    return view('kategoris.index', compact('data'));
}

    public function create()
    {
        return view('kategoris.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nama' => 'required|string|max:255',
            'keterangan' => 'nullable|string',
        ]);

        Kategori::create($data);

        return redirect()->route('kategoris.index')->with('success','Kategori ditambahkan');
    }

    private function findByHash($hashid)
    {
        $id = app(HashIdService::class)->decode($hashid);
        abort_if(!$id, 404);
        return Kategori::findOrFail($id);
    }

    public function show($hashid)
    {
        $kategori = $this->findByHash($hashid);
        return view('kategoris.show', compact('kategori'));
    }

    public function edit($hashid)
    {
        $kategori = $this->findByHash($hashid);
        return view('kategoris.edit', compact('kategori'));
    }

    public function update(Request $request, $hashid)
    {
        $kategori = $this->findByHash($hashid);

        $data = $request->validate([
            'nama' => 'required|string|max:255',
            'keterangan' => 'nullable|string',
        ]);

        $kategori->update($data);

        return redirect()->route('kategoris.index')->with('success','Kategori diperbarui');
    }

    public function destroy($hashid)
    {
        $kategori = $this->findByHash($hashid);
        $kategori->delete();

        return back()->with('success','Kategori dihapus');
    }
}
