<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class CopyFontsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fonts:copy';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Copy NotoSansJP fonts into public/storage/fonts for PDF usage';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $source = resource_path('fonts/NotoSansJP');
        $destination = public_path('storage/fonts');

        if (!File::exists($source)) {
            $this->error("❌ Folder font tidak ditemukan di: $source");
            return Command::FAILURE;
        }

        // Buat folder tujuan jika belum ada
        if (!File::exists($destination)) {
            File::makeDirectory($destination, 0755, true);
        }

        // Copy semua file font
        $files = File::files($source);
        foreach ($files as $file) {
            $target = $destination . '/' . $file->getFilename();
            File::copy($file->getPathname(), $target);
        }

        $this->info("✅ Semua font berhasil dicopy ke $destination");
        return Command::SUCCESS;
    }
}
