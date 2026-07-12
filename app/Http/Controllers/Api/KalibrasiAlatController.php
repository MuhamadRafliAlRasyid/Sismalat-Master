<?php

namespace App\Http\Controllers\Api;

use App\Models\Alat;
use App\Models\KalibrasiAlat;
use App\Services\HashIdService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class KalibrasiAlatController extends Controller
{
    protected function decode($hashid)
    {
        $id = app(HashIdService::class)->decode($hashid);
        if (!$id) {
            throw new ModelNotFoundException('Data tidak ditemukan.');
        }
        return $id;
    }

    /* ================= INDEX ================= */
    public function index(Request $request)
    {
        Log::info('🔍 [KalibrasiAlatController@index] Request received', [
            'params' => $request->all(),
        ]);

        try {
            $query = KalibrasiAlat::with('alat');

            if ($request->search) {
                Log::info('🔍 [KalibrasiAlatController@index] Searching: ' . $request->search);
                $query->whereHas('alat', function ($q) use ($request) {
                    $q->where('nama_alat', 'like', '%' . $request->search . '%');
                });
            }

            $data = $query->latest()->paginate(10)->withQueryString();

            Log::info('✅ [KalibrasiAlatController@index] Success', [
                'total' => $data->total(),
            ]);

            return response()->json([
                'success' => true,
                'data' => $data
            ]);
        } catch (\Exception $e) {
            Log::error('❌ [KalibrasiAlatController@index] Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data kalibrasi: ' . $e->getMessage(),
                'data' => []
            ], 500);
        }
    }

    /* ================= INDEX BY ALAT ================= */
    public function indexByAlat($hashid, Request $request)
    {
        Log::info('🔍 [KalibrasiAlatController@indexByAlat] Request for hashid: ' . $hashid);

        try {
            $alatId = $this->decode($hashid);

            $query = KalibrasiAlat::with('alat')
                ->where('alat_id', $alatId);

            if ($request->search) {
                $query->where(function ($q) use ($request) {
                    $q->where('no_sertifikat', 'like', '%' . $request->search . '%')
                      ->orWhere('keterangan', 'like', '%' . $request->search . '%');
                });
            }

            $data = $query->latest('tanggal_kalibrasi')->paginate($request->get('per_page', 10));

            Log::info('✅ [KalibrasiAlatController@indexByAlat] Success', [
                'alat_id' => $alatId,
                'total' => $data->total(),
            ]);

            return response()->json([
                'success' => true,
                'data' => $data
            ]);
        } catch (ModelNotFoundException $e) {
            Log::error('❌ [KalibrasiAlatController@indexByAlat] Not found: ' . $hashid);
            return response()->json([
                'success' => false,
                'message' => 'Alat tidak ditemukan.'
            ], 404);
        } catch (\Exception $e) {
            Log::error('❌ [KalibrasiAlatController@indexByAlat] Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data: ' . $e->getMessage()
            ], 500);
        }
    }

    /* ================= CREATE ================= */
    public function create($hashid)
    {
        Log::info('🔍 [KalibrasiAlatController@create] Request for hashid: ' . $hashid);

        try {
            $alat = Alat::findOrFail($this->decode($hashid));

            return response()->json([
                'success' => true,
                'data' => $alat
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Alat tidak ditemukan.'
            ], 404);
        }
    }

    /* ================= STORE ================= */
    public function store(Request $request, $hashid)
    {
        Log::info('🔍 [KalibrasiAlatController@store] Request for hashid: ' . $hashid, [
            'data' => $request->all(),
        ]);

        try {
            $alat = Alat::findOrFail($this->decode($hashid));

            $validator = Validator::make($request->all(), [
                'tanggal_kalibrasi' => 'required|date|before_or_equal:today',
                'masa_berlaku_baru' => 'required|date',
                'no_sertifikat' => 'nullable|string|max:255',
                'keterangan' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                Log::warning('⚠️ [KalibrasiAlatController@store] Validation failed', [
                    'errors' => $validator->errors(),
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 422);
            }

            $validated = $validator->validated();

            $tanggalKalibrasi = Carbon::parse($validated['tanggal_kalibrasi']);
            $masaBerlakuBaru = Carbon::parse($validated['masa_berlaku_baru']);

            $lastKalibrasi = KalibrasiAlat::where('alat_id', $alat->id)
                ->latest('tanggal_kalibrasi')
                ->first();

            $customErrors = [];
            if ($lastKalibrasi && $tanggalKalibrasi->lt($lastKalibrasi->tanggal_kalibrasi)) {
                $customErrors['tanggal_kalibrasi'] = ['Tanggal tidak boleh lebih lama dari sebelumnya'];
            }

            if ($masaBerlakuBaru->lte($tanggalKalibrasi)) {
                $customErrors['masa_berlaku_baru'] = ['Masa berlaku harus setelah tanggal kalibrasi'];
            }

            if ($lastKalibrasi && $masaBerlakuBaru->lte($lastKalibrasi->masa_berlaku_baru)) {
                $customErrors['masa_berlaku_baru'] = array_merge(
                    $customErrors['masa_berlaku_baru'] ?? [],
                    ['Harus lebih besar dari masa berlaku sebelumnya']
                );
            }

            if (!empty($customErrors)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $customErrors
                ], 422);
            }

            $kalibrasi = null;
            DB::transaction(function () use ($alat, $validated, &$kalibrasi) {
                $kalibrasi = KalibrasiAlat::create([
                    'alat_id' => $alat->id,
                    ...$validated
                ]);

                $alat->update([
                    'masa_berlaku' => $validated['masa_berlaku_baru'],
                    'last_notified_at' => null
                ]);

                DB::table('notifications')
                    ->where('data->alat_id', $alat->id)
                    ->whereNull('read_at')
                    ->update(['read_at' => now()]);
            });

            Log::info('✅ [KalibrasiAlatController@store] Success', [
                'kalibrasi_id' => $kalibrasi->id,
                'alat_id' => $alat->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Kalibrasi berhasil disimpan',
                'data' => $kalibrasi->load('alat')
            ], 201);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan.'
            ], 404);
        } catch (\Exception $e) {
            Log::error('❌ [KalibrasiAlatController@store] Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan kalibrasi: ' . $e->getMessage()
            ], 500);
        }
    }

    /* ================= SHOW ================= */
    public function show($hashid)
    {
        Log::info('🔍 [KalibrasiAlatController@show] Request for hashid: ' . $hashid);

        try {
            $id = $this->decode($hashid);
            $data = KalibrasiAlat::with('alat')->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $data
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan.'
            ], 404);
        }
    }

    /* ================= EDIT ================= */
    public function edit($hashid)
    {
        Log::info('🔍 [KalibrasiAlatController@edit] Request for hashid: ' . $hashid);

        try {
            $data = KalibrasiAlat::with('alat')
                ->findOrFail($this->decode($hashid));

            return response()->json([
                'success' => true,
                'data' => $data
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan.'
            ], 404);
        }
    }

    /* ================= UPDATE ================= */
    public function update(Request $request, $hashid)
    {
        Log::info('🔍 [KalibrasiAlatController@update] Request for hashid: ' . $hashid, [
            'data' => $request->all(),
        ]);

        try {
            $data = KalibrasiAlat::with('alat')->findOrFail($this->decode($hashid));
            $alat = $data->alat;

            $validator = Validator::make($request->all(), [
                'tanggal_kalibrasi' => 'required|date|before_or_equal:today',
                'masa_berlaku_baru' => 'required|date',
                'no_sertifikat' => 'nullable|string|max:255',
                'keterangan' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 422);
            }

            $validated = $validator->validated();

            $tanggalKalibrasi = Carbon::parse($validated['tanggal_kalibrasi']);
            $masaBerlakuBaru = Carbon::parse($validated['masa_berlaku_baru']);

            $lastKalibrasi = KalibrasiAlat::where('alat_id', $alat->id)
                ->where('id', '!=', $data->id)
                ->latest('tanggal_kalibrasi')
                ->first();

            $customErrors = [];
            if ($lastKalibrasi && $tanggalKalibrasi->lt($lastKalibrasi->tanggal_kalibrasi)) {
                $customErrors['tanggal_kalibrasi'] = ['Tanggal tidak boleh lebih lama dari data lain'];
            }

            if ($masaBerlakuBaru->lte($tanggalKalibrasi)) {
                $customErrors['masa_berlaku_baru'] = ['Masa berlaku harus setelah tanggal kalibrasi'];
            }

            if ($lastKalibrasi && $masaBerlakuBaru->lte($lastKalibrasi->masa_berlaku_baru)) {
                $customErrors['masa_berlaku_baru'] = array_merge(
                    $customErrors['masa_berlaku_baru'] ?? [],
                    ['Harus lebih besar dari data sebelumnya']
                );
            }

            if (!empty($customErrors)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $customErrors
                ], 422);
            }

            DB::transaction(function () use ($data, $alat, $validated) {
                $data->update($validated);

                $latest = KalibrasiAlat::where('alat_id', $alat->id)
                    ->latest('tanggal_kalibrasi')
                    ->first();

                if ($latest && $latest->id == $data->id) {
                    $alat->update([
                        'masa_berlaku' => $validated['masa_berlaku_baru']
                    ]);

                    DB::table('notifications')
                        ->where('data->alat_id', $alat->id)
                        ->whereNull('read_at')
                        ->update(['read_at' => now()]);
                }
            });

            Log::info('✅ [KalibrasiAlatController@update] Success', [
                'kalibrasi_id' => $data->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Kalibrasi berhasil diupdate',
                'data' => $data->fresh()->load('alat')
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan.'
            ], 404);
        } catch (\Exception $e) {
            Log::error('❌ [KalibrasiAlatController@update] Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengupdate kalibrasi: ' . $e->getMessage()
            ], 500);
        }
    }

    /* ================= DELETE ================= */
    public function destroy($hashid)
    {
        Log::info('🔍 [KalibrasiAlatController@destroy] Request for hashid: ' . $hashid);

        try {
            $data = KalibrasiAlat::with('alat')->findOrFail($this->decode($hashid));
            $alat = $data->alat;

            DB::transaction(function () use ($data, $alat) {
                $data->delete();

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
            });

            Log::info('✅ [KalibrasiAlatController@destroy] Success', [
                'kalibrasi_id' => $data->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Data kalibrasi dihapus'
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan.'
            ], 404);
        } catch (\Exception $e) {
            Log::error('❌ [KalibrasiAlatController@destroy] Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus kalibrasi: ' . $e->getMessage()
            ], 500);
        }
    }
}