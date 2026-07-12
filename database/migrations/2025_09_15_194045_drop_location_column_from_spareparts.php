<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropLocationColumnFromSpareparts extends Migration
{
    public function up()
    {
        Schema::table('spareparts', function (Blueprint $table) {
            $table->dropColumn('location');
        });
    }

    public function down()
    {
        Schema::table('spareparts', function (Blueprint $table) {
            $table->string('location', 255)->nullable()->after('pic');
        });
    }
}
