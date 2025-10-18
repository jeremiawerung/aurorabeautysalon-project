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
    if (!Schema::hasTable('layanan')) {
        Schema::create('layanan', function (Blueprint $table) {
            $table->id('id_layanan');
            $table->foreignId('id_admin')->nullable()->constrained('admin', 'id_admin')->onDelete('set null')->onUpdate('cascade');
            $table->foreignId('id_kategoriLayanan')->nullable()->constrained('kategori_layanan')->onDelete('set null')->onUpdate('cascade');
            $table->string('nama_layanan', 60);
            $table->decimal('harga', 10, 2);
            $table->text('deskripsi')->nullable();
            $table->integer('durasi');
            $table->string('status_layanan', 35);
            $table->timestamps();
        });
    }
}

    public function down()
    {
        Schema::dropIfExists('layanan');
    }

};
