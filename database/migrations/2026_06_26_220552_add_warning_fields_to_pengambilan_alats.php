<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pengambilan_alats', function (Blueprint $table) {
            // Tracking terpisah untuk masing-masing jenis warning
            $table->timestamp('warning_15_sent_at')->nullable()->after('last_warned_at');
            $table->timestamp('warning_1day_sent_at')->nullable()->after('warning_15_sent_at');
            $table->boolean('is_critical')->default(false)->after('warning_1day_sent_at');
        });
    }

    public function down(): void
    {
        Schema::table('pengambilan_alats', function (Blueprint $table) {
            $table->dropColumn(['warning_15_sent_at', 'warning_1day_sent_at', 'is_critical']);
        });
    }
};
