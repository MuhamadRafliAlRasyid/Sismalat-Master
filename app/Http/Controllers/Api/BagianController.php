<?php

namespace App\Http\Controllers\Api;

use App\Models\Bagian;
use Illuminate\Http\Request;
use App\Services\HashIdService;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;

class BagianController extends Controller
{
    protected function resolveHashid($hashid)
    {
        $id = app(HashIdService::class)->decode($hashid);
        if (!$id) {
            abort(404, 'Hash ID tidak valid');
        }
        return $id;
    }


    public function index()
    {
        $bagian = Bagian::all();

        return response()->json([
            'status' => true,
            'data' => $bagian->map(function ($b) {
                return [
                    'id' => $b->id,
                    'hashid' => app(HashIdService::class)->encode($b->id), // ✅ WAJIB
                    'nama' => $b->nama,
                ];
            })
        ]);
    }

    /**
     * Store a newly created resource.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'nama' => 'required|string|max:255|unique:bagian,nama',
                'deskripsi'   => 'nullable|string|max:500',
            ]);

            $bagian = Bagian::create($validated);

            Log::info('Bagian created successfully', [
                'bagian_id' => $bagian->id,
                'nama' => $bagian->nama
            ]);

            return response()->json([
                'status'  => true,
                'message' => 'Bagian berhasil ditambahkan',
                'data'    => $bagian
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Validasi gagal',
                'errors'  => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Bagian Store Error: ' . $e->getMessage());
            return response()->json([
                'status'  => false,
                'message' => 'Gagal menyimpan data bagian'
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($hashid)
{
    $id = app(HashIdService::class)->decode($hashid);

    if (!$id) {
        return response()->json([
            'status' => false,
            'message' => 'Hash ID tidak valid'
        ], 404);
    }

    $bagian = Bagian::find($id);

    if (!$bagian) {
        return response()->json([
            'status' => false,
            'message' => 'Bagian tidak ditemukan'
        ], 404);
    }

    return response()->json([
        'status' => true,
        'data' => $bagian
    ]);
}

    /**
     * Update the specified resource.
     */
    public function update(Request $request, $hashid)
{
    $id = app(HashIdService::class)->decode($hashid);

    $bagian = Bagian::find($id);

    if (!$bagian) {
        return response()->json([
            'status' => false,
            'message' => 'Bagian tidak ditemukan'
        ], 404);
    }

    $bagian->update([
        'nama' => $request->nama
    ]);

    return response()->json([
        'status' => true,
        'message' => 'Berhasil update',
        'data' => $bagian
    ]);
}

    /**
     * Remove the specified resource.
     */
    public function destroy($hashid)
{
    $id = app(HashIdService::class)->decode($hashid);

    $bagian = Bagian::find($id);

    if (!$bagian) {
        return response()->json([
            'status' => false,
            'message' => 'Bagian tidak ditemukan'
        ], 404);
    }

    $bagian->delete();

    return response()->json([
        'status' => true,
        'message' => 'Berhasil dihapus'
    ]);
}
}
