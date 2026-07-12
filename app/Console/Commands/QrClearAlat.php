<?php

namespace App\Console\Commands;

use App\Models\Alat;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class QrClearAlat extends Command
{
    protected $signature = 'qr:clear-alat';
    protected $description = 'Hapus semua file QR Code alat dan kosongkan kolom qr_code di database';

    public function handle(): int
    {
        $alats = Alat::whereNotNull('qr_code')->get();

        if ($alats->isEmpty()) {
            $this->info('✅ Tidak ada QR code yang perlu dihapus.');
            return 0;
        }

        $this->info("🔧 Menghapus QR code untuk {$alats->count()} alat...");

        $bar = $this->output->createProgressBar($alats->count());
        $bar->start();

        foreach ($alats as $alat) {
            if ($alat->qr_code && Storage::disk('public')->exists($alat->qr_code)) {
                Storage::disk('public')->delete($alat->qr_code);
            }

            $alat->update(['qr_code' => null]);
            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);
        $this->info('✅ Semua QR code alat telah dihapus.');

        return 0;
    }
}
