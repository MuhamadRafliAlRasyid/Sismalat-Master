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
        Schema::create('spareparts', function (Blueprint $table) {
            $table->id();
            $table->string('nama_part');
            $table->string('model')->nullable();
            $table->string('merk')->nullable();
            $table->integer('jumlah_baru')->nullable()->default(0);
            $table->integer('jumlah_bekas')->nullable()->default(0);
            $table->string('supplier')->nullable();
            $table->decimal('patokan_harga')->nullable();
            $table->decimal('total')->nullable()->default(0);
            $table->string('ruk_no')->nullable();
            $table->date('purchase_date')->nullable();
            $table->date('delivery_date')->nullable();
            $table->string('po_number')->nullable();
            $table->string('titik_pesanan')->nullable();
            $table->integer('jumlah_pesanan')->nullable();
            $table->boolean('cek')->nullable()->default(false);
            $table->string('pic')->nullable();
            $table->text('qr_code')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('spareparts');
    }
};
