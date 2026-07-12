<?php

namespace App\Exports;

use App\Models\Alat;
use Maatwebsite\Excel\Concerns\FromCollection;

class AlatExport implements FromCollection
{
    public function collection()
    {
        return Alat::with('kategori')->get();
    }
}
