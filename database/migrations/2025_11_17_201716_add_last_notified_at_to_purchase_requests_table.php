<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('purchase_requests', function (Blueprint $table) {
            $table->timestamp('last_notified_at')->nullable()->after('status');
        });
    }

    public function down()
    {
        Schema::table('purchase_requests', function (Blueprint $table) {
            $table->dropColumn('last_notified_at');
        });
    }
};
