<?php

namespace App\Http\Controllers\Api;

use App\Models\Kategori;
use Illuminate\Http\Request;
use App\Services\HashIdService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class KategoriController extends Controller
{
    /* ================= INDEX ================= */
    public function index(Request $request)
    {
        Log::info('🔍 [KategoriController@index] Request received', [
            'params' => $request->all(),
        ]);

        try {
            $query = Kategori::query();

            if ($request->filled('search')) {
                $search = $request->search;
                Log::info('🔍 [KategoriController@index] Searching: ' . $search);

                $query->where(function ($q) use ($search) {
                    $q->where('nama', 'like', '%' . $search . '%')
                      ->orWhere('keterangan', 'like', '%' . $search . '%');
                });
            }

            $data = $query->latest()->paginate(10)->withQueryString();

            Log::info('✅ [KategoriController@index] Success', [
                'total' => $data->total(),
            ]);

            return response()->json([
                'success' => true,
                'data' => $data
            ]);
        } catch (\Exception $e) {
            Log::error('❌ [KategoriController@index] Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data kategori: ' . $e->getMessage(),
                'data' => []
            ], 500);
        }
    }

    /* ================= CREATE ================= */
    public function create()
    {
        Log::info('🔍 [KategoriController@create] Request received');

        return response()->json([
            'success' => true,
            'data' => [
                'fields' => [
                    'nama' => ['type' => 'string', 'required' => true, 'max' => 255],
                    'keterangan' => ['type' => 'string', 'required' => false],
                ]
            ]
        ]);
    }

    /* ================= STORE ================= */
    public function store(Request $request)
    {
        Log::info('🔍 [KategoriController@store] Request received', [
            'data' => $request->all(),
        ]);

        try {
            $validator = Validator::make($request->all(), [
                'nama' => 'required|string|max:255',
                'keterangan' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                Log::warning('⚠️ [KategoriController@store] Validation failed', [
                    'errors' => $validator->errors(),
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 422);
            }

            $kategori = Kategori::create($validator->validated());

            Log::info('✅ [KategoriController@store] Success', [
                'kategori_id' => $kategori->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Kategori berhasil ditambahkan.',
                'data' => $kategori
            ], 201);
        } catch (\Exception $e) {
            Log::error('❌ [KategoriController@store] Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal menambahkan kategori: ' . $e->getMessage()
            ], 500);
        }
    }

    /* ================= HELPER ================= */
    private function findByHash($hashid)
    {
        $id = app(HashIdService::class)->decode($hashid);
        if (!$id) {
            throw new ModelNotFoundException('Kategori tidak ditemukan.');
        }
        return Kategori::findOrFail($id);
    }

    /* ================= SHOW ================= */
    public function show($hashid)
    {
        Log::info('🔍 [KategoriController@show] Request for hashid: ' . $hashid);

        try {
            $kategori = $this->findByHash($hashid);

            return response()->json([
                'success' => true,
                'data' => $kategori
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Kategori tidak ditemukan.'
            ], 404);
        }
    }

    /* ================= EDIT ================= */
    public function edit($hashid)
    {
        Log::info('🔍 [KategoriController@edit] Request for hashid: ' . $hashid);

        try {
            $kategori = $this->findByHash($hashid);

            return response()->json([
                'success' => true,
                'data' => $kategori
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Kategori tidak ditemukan.'
            ], 404);
        }
    }

    /* ================= UPDATE ================= */
    public function update(Request $request, $hashid)
    {
        Log::info('🔍 [KategoriController@update] Request for hashid: ' . $hashid, [
            'data' => $request->all(),
        ]);

        try {
            $kategori = $this->findByHash($hashid);

            $validator = Validator::make($request->all(), [
                'nama' => 'required|string|max:255',
                'keterangan' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 422);
            }

            $kategori->update($validator->validated());

            Log::info('✅ [KategoriController@update] Success', [
                'kategori_id' => $kategori->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Kategori berhasil diperbarui.',
                'data' => $kategori
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Kategori tidak ditemukan.'
            ], 404);
        } catch (\Exception $e) {
            Log::error('❌ [KategoriController@update] Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui kategori: ' . $e->getMessage()
            ], 500);
        }
    }

    /* ================= DESTROY ================= */
    public function destroy($hashid)
    {
        Log::info('🔍 [KategoriController@destroy] Request for hashid: ' . $hashid);

        try {
            $kategori = $this->findByHash($hashid);
            $kategori->delete();

            Log::info('✅ [KategoriController@destroy] Success', [
                'kategori_id' => $kategori->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Kategori berhasil dihapus.'
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Kategori tidak ditemukan.'
            ], 404);
        } catch (\Exception $e) {
            Log::error('❌ [KategoriController@destroy] Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus kategori: ' . $e->getMessage()
            ], 500);
        }
    }
}