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
        if (! Schema::hasTable('pelanggan')) {
            Schema::create('pelanggan', function (Blueprint $table) {
                $table->id('id_pelanggan');
                $table->string('nama', 60);
                $table->string('nomor_telepon', 15);
                $table->string('email', 65)->nullable()->unique();
                $table->string('password')->nullable();
                $table->date('tanggal_daftar')->default(now()->toDateString())->nullable(); // default untuk tanggal daftar
                $table->string('status_pelanggan', 35)->nullable();
                $table->timestamps();
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('pelanggan');
    }
};
