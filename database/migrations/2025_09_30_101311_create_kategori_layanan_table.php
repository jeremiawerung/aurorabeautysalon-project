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
        // Cek apakah tabel sudah ada sebelum membuat
        if (!Schema::hasTable('kategori_layanan')) {
            Schema::create('kategori_layanan', function (Blueprint $table) {
                $table->id('id_kategoriLayanan');
                $table->foreignId('id_admin')->nullable()->constrained('admin', 'id_admin')->onDelete('set null')->onUpdate('cascade');
                $table->string('nama', 60);
                $table->string('status', 35);
                $table->text('keterangan')->nullable();
                $table->dateTime('tanggal_update')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('kategori_layanan');
    }



};
