<?php

namespace App\Http\Controllers;

use App\Models\Bagian;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class BagiansController extends Controller
{
    public function index(Request $request)
    {
        $query = Bagian::query();

        // Filter berdasarkan pencarian
        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('nama_bagian', 'like', "%{$search}%")
                    ->orWhere('deskripsi', 'like', "%{$search}%");
            });
        }

        $bagians = $query->paginate(10)->withQueryString();
        Log::info('Index Bagians: ', $bagians->toArray());

        return view('bagian.index', compact('bagians'));
    }

    public function create()
    {
        return view('bagian.create');
    }

    public function store(Request $request)
    {
        Log::info('Store Request: ', $request->all());
        $request->validate([
            'nama' => 'required|string|max:255|unique:bagian,nama',
        ]);

        $bagian = Bagian::create($request->all());
        Log::info('Stored Bagian: ', $bagian->toArray());
        return redirect()->route('bagian.index')->with('success', 'Bagian berhasil ditambahkan.');
    }

    // public function show($id)
    // {
    //     $bagian = Bagian::findOrFail($id);
    //     return view('bagian.show', compact('bagian'));
    // }


    public function edit(Bagian $bagian)
    {
        if (!$bagian || !$bagian->exists) {
            Log::error('Edit: Bagian tidak ditemukan untuk ID: ' . request()->route('bagian') ?? 'null');
            abort(404, 'Bagian tidak ditemukan.');
        }
        Log::info('Edit Bagian: ', $bagian->toArray());
        return view('bagian.edit', compact('bagian'));
    }

    public function update(Request $request, Bagian $bagian)
    {
        Log::debug('Update: Received Bagian instance', ['bagian' => $bagian->toArray() ?? 'null', 'route_id' => request()->route('bagian')]);
        if (!$bagian || !$bagian->exists) {
            Log::error('Update: Bagian not found for ID: ' . request()->route('bagian'));
            abort(404, 'Bagian tidak ditemukan.');
        }
        Log::info('Update Request: ', $request->all());
        Log::info('Update Bagian Before: ', $bagian->toArray());

        $request->validate([
            'nama' => 'required|string|max:255|unique:bagian,nama,' . $bagian->id,
        ]);

        $bagian->update($request->all());
        Log::info('Update Bagian After: ', $bagian->fresh()->toArray());
        return redirect()->route('bagian.index')->with('success', 'Bagian berhasil diperbarui.');
    }

    public function destroy(Bagian $bagian)
    {
        if (!$bagian || !$bagian->exists) {
            Log::error('Destroy: Bagian tidak ditemukan untuk ID: ' . request()->route('id') ?? 'null');
            abort(404, 'Bagian tidak ditemukan.');
        }
        $bagian->delete();
        Log::info('Destroyed Bagian ID: ', ['id' => $bagian->id]);
        return redirect()->route('bagian.index')->with('success', 'Bagian berhasil dihapus.');
    }
}
