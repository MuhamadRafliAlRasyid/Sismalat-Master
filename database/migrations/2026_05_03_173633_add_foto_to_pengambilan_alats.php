<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pengambilan_alats', function (Blueprint $table) {
            $table->string('foto')->nullable()->after('keperluan');
        });
    }

    public function down(): void
    {
        Schema::table('pengambilan_alats', function (Blueprint $table) {
            $table->dropColumn('foto');
        });
    }
};
