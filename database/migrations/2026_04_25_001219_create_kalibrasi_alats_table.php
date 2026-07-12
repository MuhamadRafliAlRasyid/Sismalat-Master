<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('kalibrasi_alats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('alat_id')->constrained()->cascadeOnDelete();
            $table->date('tanggal_kalibrasi');
            $table->date('masa_berlaku_baru');
            $table->string('no_sertifikat')->nullable();
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kalibrasi_alats');
    }
};
