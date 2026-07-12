<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void
    {
        Schema::dropIfExists('alats');

        Schema::create('alats', function (Blueprint $table) {
            $table->id();

            // 🔥 IDENTITAS
            $table->string('nama_alat');
            $table->string('kelas')->nullable();
            $table->string('merk')->nullable();
            $table->string('tipe')->nullable();

            // 🔥 IDENTIFIKASI
            $table->string('no_seri')->nullable();
            $table->string('no_identitas')->nullable();

            // 🔥 SPESIFIKASI
            $table->string('kapasitas')->nullable();
            $table->string('daya_baca')->nullable();

            // 🔥 JUMLAH
            $table->integer('jumlah')->default(0);

            // 🔥 SERTIFIKAT
            $table->string('no_sertifikat')->nullable();
            $table->date('masa_berlaku')->nullable();

            // 🔥 RELASI
            $table->foreignId('kategori_id')
                ->nullable()
                ->constrained('kategoris')
                ->nullOnDelete();

            // 🔥 NOTIF
            $table->timestamp('last_notified_at')->nullable();

            // 🔥 QR
            $table->string('qr_code')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alats');
    }
};
