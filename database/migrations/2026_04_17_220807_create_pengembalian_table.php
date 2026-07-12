<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('pengembalian', function (Blueprint $table) {
            $table->id();
            $table->string('hashid')->unique();

            $table->foreignId('pengambilan_id')
                  ->constrained('pengambilan_spareparts')
                  ->onDelete('cascade');

            $table->foreignId('sparepart_id')
                  ->constrained('spareparts')
                  ->onDelete('cascade');

            $table->foreignId('user_id')
                  ->constrained('users')
                  ->onDelete('cascade');

            $table->integer('jumlah_dikembalikan');
            $table->enum('kondisi', ['baik', 'rusak']);
            $table->text('alasan');
            $table->text('keterangan')->nullable();

            $table->timestamp('tanggal_kembali')->useCurrent();

            $table->timestamps();

            // Index
            $table->index(['pengambilan_id', 'sparepart_id', 'user_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('pengembalian');
    }
};
