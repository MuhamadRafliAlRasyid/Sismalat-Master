<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('pengembalian_alats', function (Blueprint $table) {
            $table->id();

            $table->foreignId('pengambilan_alat_id')
                ->constrained('pengambilan_alats')
                ->cascadeOnDelete();

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->integer('jumlah');
            $table->dateTime('tanggal_pengembalian');

            $table->text('keterangan')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pengembalian_alats');
    }
};
