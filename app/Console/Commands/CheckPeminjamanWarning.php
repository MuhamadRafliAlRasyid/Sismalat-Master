<?php

namespace App\Console\Commands;

use App\Http\Controllers\PengambilanAlatController;
use Illuminate\Console\Command;

class CheckPeminjamanWarning extends Command
{
    protected $signature = 'peminjaman:check-warning';
    protected $description = 'Cek dan kirim notifikasi warning peminjaman yang mendekati batas waktu';

    public function handle(): int
    {
        $controller = app(PengambilanAlatController::class);
        $controller->checkPeminjamanWarning();

        $this->info('Pengecekan warning peminjaman selesai.');
        return 0;
    }
}
