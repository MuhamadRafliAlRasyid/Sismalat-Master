<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::table('pengambilan_alats', function (Blueprint $table) {
        $table->string('nama_peminjam')->nullable()->after('user_id');
    });
}
public function down()
{
    Schema::table('pengambilan_alats', function (Blueprint $table) {
        $table->dropColumn('nama_peminjam');
    });
}
};
