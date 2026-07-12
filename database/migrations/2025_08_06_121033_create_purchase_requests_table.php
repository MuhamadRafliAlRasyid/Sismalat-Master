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
        Schema::create('purchase_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('nama_part');
            $table->string('part_number');
            $table->text('link_website')->nullable();
            $table->date('waktu_request');
            $table->integer('quantity');
            $table->string('satuan');
            $table->date('mas_deliver');
            $table->string('untuk_apa');
            $table->string('pic');
            $table->string('quotation_lead_time')->nullable();
            $table->enum('status', ['PR', 'PO'])->default('PR');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_requests');
    }
};
