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
        Schema::table('pelanggan', function (Blueprint $table) {
            // Ubah kolom status_pelanggan menjadi nullable
            $table->string('status_pelanggan', 35)->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table('pelanggan', function (Blueprint $table) {
            // Kembalikan kolom status_pelanggan menjadi non-nullable jika migration di-rollback
            $table->string('status_pelanggan', 35)->nullable(false)->change();
        });
    }

};
