<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('pengambilan_alats', function (Blueprint $table) {
    $table->id();

    $table->foreignId('user_id')->constrained()->cascadeOnDelete();

    $table->foreignId('bagian_id')
        ->constrained('bagian')
        ->cascadeOnDelete();

    $table->foreignId('alat_id')->constrained()->cascadeOnDelete();

    // 🔥 TAMBAHAN
    $table->integer('jumlah')->default(1);
    $table->string('satuan')->default('unit');

    $table->text('keperluan');
    $table->dateTime('waktu_pengambilan');

    $table->enum('status', ['dipinjam', 'kembali'])->default('dipinjam');

    $table->timestamps();
});
    }

    public function down(): void
    {
        Schema::dropIfExists('pengambilan_alats');
    }
};
