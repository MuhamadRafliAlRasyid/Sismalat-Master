<?php

namespace App\Http\Controllers;

use App\Models\Alat;
use App\Models\PengambilanAlat;
use App\Models\PengembalianAlat;
use Illuminate\Http\Request;

class AdminDashboardController extends Controller
{
    public function index()
    {
        $totalAlat = Alat::count();
        $totalDipinjam = PengambilanAlat::sum('jumlah') ?: 0;
        $totalDikembalikan = PengembalianAlat::sum('jumlah') ?: 0;

        return view('admin.dashboard', [
            'totalAlat' => $totalAlat,
            'totalDipinjam' => $totalDipinjam,
            'totalDikembalikan' => $totalDikembalikan,
        ]);
    }
}
