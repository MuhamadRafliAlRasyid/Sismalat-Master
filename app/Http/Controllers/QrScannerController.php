<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Alat;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Services\HashIdService;
use Vinkla\Hashids\Facades\Hashids; // tambahkan

class QrScannerController extends Controller
{
    protected $hashIdService;

    public function __construct(HashIdService $hashIdService)
    {
        $this->hashIdService = $hashIdService;
    }

    public function index()
    {
        return view('qr-scanner');
    }

 public function process(Request $request)
{
    $request->validate([
        'hashid' => 'required|string|max:255'
    ]);

    $originalHashid = trim($request->input('hashid'));

    // Ekstrak hashid dari URL jika ada
    $cleanHashid = $originalHashid;
    if (filter_var($cleanHashid, FILTER_VALIDATE_URL)) {
        $parsed = parse_url($cleanHashid);
        if (isset($parsed['query'])) {
            parse_str($parsed['query'], $queryParams);
            if (isset($queryParams['alat_id'])) {
                $cleanHashid = $queryParams['alat_id'];
            }
        }
    }

    // Decode hashid menggunakan Hashids
    $id = Hashids::connection('main')->decode($cleanHashid);
    $id = $id[0] ?? null;

    if (!$id) {
        Log::warning('Gagal decode hashid: ' . $cleanHashid);
        return response()->json([
            'success' => false,
            'message' => 'QR Code tidak valid.'
        ], 404);
    }

    $alat = Alat::find($id);
    if (!$alat) {
        Log::warning('Alat tidak ditemukan untuk ID: ' . $id);
        return response()->json([
            'success' => false,
            'message' => 'Alat tidak ditemukan.'
        ], 404);
    }

    // Cek login
    if (!Auth::check()) {
        // Arahkan ke login, bawa hashid asli
        $redirectUrl = route('login', ['alat_id' => $originalHashid]);
    } else {
        // Sudah login → arahkan ke form pengambilan alat dengan hashid
        $redirectUrl = route('pengambilan_alat.create', ['alat_hashid' => $alat->hashid]); // <-- perbaikan
    }

    return response()->json([
        'success' => true,
        'redirect' => $redirectUrl
    ]);
}
}
