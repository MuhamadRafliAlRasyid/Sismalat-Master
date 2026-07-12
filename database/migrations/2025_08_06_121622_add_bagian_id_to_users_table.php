<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('bagian_id')->nullable()->constrained('bagian')->onUpdate('cascade')->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['bagian_id']);
            $table->dropColumn('bagian_id');
        });
    }
};
