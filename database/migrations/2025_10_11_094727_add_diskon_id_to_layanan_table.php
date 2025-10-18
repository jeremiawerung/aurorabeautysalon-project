<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDiskonIdToLayananTable extends Migration
{
    public function up()
    {
        Schema::table('layanan', function (Blueprint $table) {
            $table->foreignId('id_diskon')->nullable()->constrained('diskon')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('layanan', function (Blueprint $table) {
            $table->dropForeign(['id_diskon']);
            $table->dropColumn('id_diskon');
        });
    }
};
