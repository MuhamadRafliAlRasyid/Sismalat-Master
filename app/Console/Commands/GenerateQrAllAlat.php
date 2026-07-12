<?php

namespace App\Console\Commands;

use App\Models\Alat;
use Endroid\QrCode\Color\Color;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class GenerateQrAllAlat extends Command
{
    protected $signature = 'qr:generate-alat {--force : Regenerate QR even if already exists}';
    protected $description = 'Generate QR Code untuk semua alat yang belum memiliki QR';

    public function handle(): int
    {
        $query = Alat::query();

        if (!$this->option('force')) {
            $query->whereNull('qr_code');
        }

        $alats = $query->get();

        if ($alats->isEmpty()) {
            $this->info('✅ Tidak ada alat yang perlu di-generate QR.');
            return 0;
        }

        $this->info('🔧 Memproses ' . $alats->count() . ' alat...');

        $bar = $this->output->createProgressBar($alats->count());
        $bar->start();

        // Pastikan direktori qrcodes ada
        Storage::makeDirectory('public/qrcodes', 0755, true);

        foreach ($alats as $alat) {
            try {
                $this->generateQrCode($alat);
                $bar->advance();
            } catch (\Exception $e) {
                $this->error(' Gagal generate QR untuk alat: ' . $alat->nama_alat);
                Log::error('QR Generation failed for alat ' . $alat->id . ': ' . $e->getMessage());
            }
        }

        $bar->finish();
        $this->newLine(2);
        $this->info('✅ QR Code berhasil di-generate untuk semua alat.');

        return 0;
    }

    /**
     * Generate QR Code untuk satu alat.
     * Adaptasi dari method yang berhasil di SparepartsController.
     */
    protected function generateQrCode(Alat $alat): void
    {
        $qrCodePath = 'qrcodes/alat_' . $alat->hashid . '_' . Str::slug($alat->nama_alat) . '.png';
        $fullPath = storage_path('app/public/' . $qrCodePath);

        // Hapus QR lama jika ada
        if ($alat->qr_code && Storage::disk('public')->exists($alat->qr_code)) {
            Storage::disk('public')->delete($alat->qr_code);
        }

        // Buat QR Code (pakai named arguments, seperti di SparepartsController)
        $qrCode = new QrCode(
            data: route('login') . '?alat_id=' . $alat->hashid, // <-- langsung ke login
            encoding: new Encoding('UTF-8'),
            size: 300,
            margin: 10,
            foregroundColor: new Color(0, 0, 0),
            backgroundColor: new Color(255, 255, 255)
        );

        $writer = new PngWriter();
        $result = $writer->write($qrCode);
        $result->saveToFile($fullPath);

        // Simpan path ke database
        $alat->update(['qr_code' => $qrCodePath]);
    }
}
