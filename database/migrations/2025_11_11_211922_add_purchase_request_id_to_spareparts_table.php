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
        Schema::table('spareparts', function ($table) {
    $table->foreignId('purchase_request_id')->nullable()->constrained()->onDelete('set null');
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('spareparts', function (Blueprint $table) {
            //
        });
    }
};
