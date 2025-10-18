<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDiskonAppliedToPembayaranTable extends Migration
{
    public function up()
    {
        Schema::table('pembayaran', function (Blueprint $table) {
            $table->decimal('diskon_applied', 10, 2)->default(0)->after('jumlah');
        });
    }

    public function down()
    {
        Schema::table('pembayaran', function (Blueprint $table) {
            $table->dropColumn('diskon_applied');
        });
    }
};
