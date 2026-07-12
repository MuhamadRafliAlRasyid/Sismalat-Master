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
    Schema::table('pengambilan_spareparts', function (Blueprint $table) {
        $table->integer('jumlah_dikembalikan')->default(0)->after('jumlah');
    });
}

public function down()
{
    Schema::table('pengambilan_spareparts', function (Blueprint $table) {
        $table->dropColumn('jumlah_dikembalikan');
    });
}
};
