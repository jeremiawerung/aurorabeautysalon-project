<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsToDiskonTable extends Migration
{
    public function up()
    {
        Schema::table('diskon', function (Blueprint $table) {
            $table->string('nama_diskon')->nullable();  // Nama diskon
            $table->decimal('persentase_diskon', 5, 2)->nullable();  // Persentase diskon (misalnya 20.00 untuk 20%)
            $table->date('tanggal_mulai')->nullable();  // Tanggal mulai diskon
            $table->date('tanggal_berakhir')->nullable();  // Tanggal berakhir diskon
            $table->string('status')->default('aktif');  // Status diskon (aktif atau nonaktif)
            $table->text('keterangan')->nullable();  // Deskripsi atau keterangan tambahan diskon
        });
    }

    public function down()
    {
        Schema::table('diskon', function (Blueprint $table) {
            $table->dropColumn([
                'nama_diskon',
                'persentase_diskon',
                'tanggal_mulai',
                'tanggal_berakhir',
                'status',
                'keterangan',
            ]);
        });
    }
};
