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
        Schema::table('pengambilan_spareparts', function (Blueprint $table) {
            $table->string('part_type')->after('spareparts_id')->default('baru');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pengambilan_spareparts', function (Blueprint $table) {
            $table->string('part_type')->after('spareparts_id')->default('baru');
        });
    }
};
