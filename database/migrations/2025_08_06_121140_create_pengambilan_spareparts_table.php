<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('pengambilan_spareparts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // yang mengambil
            $table->string('bagian_id')->constrained('bagian')->onDelete('cascade'); // bagian/divisi yang membutuhkan
            $table->foreignId('spareparts_id')->constrained('spareparts')->onDelete('cascade');
            $table->integer('jumlah');
            $table->string('satuan');
            $table->string('keperluan'); // keterangan tambahan
            $table->timestamp('waktu_pengambilan');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pengambilan_barangs');
    }
};
