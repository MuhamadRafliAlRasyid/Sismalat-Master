<?php

namespace App\Http\Controllers;

use App\Models\PengambilanAlat;
use App\Models\PengembalianAlat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class KaryawanDashboardController extends Controller
{
    public function index()
    {
        $userId = Auth::id();

        $totalDipinjam = PengambilanAlat::where('user_id', $userId)
                            ->where('status', 'dipinjam')
                            ->count();

        $totalDikembalikan = PengembalianAlat::where('user_id', $userId)->count();

        $pengambilanTerbaru = PengambilanAlat::with('alat')
                            ->where('user_id', $userId)
                            ->latest()
                            ->take(5)
                            ->get();

        $alatDipinjam = PengambilanAlat::with('alat')
                            ->where('user_id', $userId)
                            ->where('status', 'dipinjam')
                            ->get();

        return view('karyawan.dashboard', compact(
            'totalDipinjam',
            'totalDikembalikan',
            'pengambilanTerbaru',
            'alatDipinjam'
        ));
    }
}
