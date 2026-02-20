<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Membuat tabel 'diskon'
        Schema::create('diskon', function (Blueprint $table) {
            $table->id();
            $table->string('nama_diskon', 255)->comment('Nama/deskripsi diskon, cth: Diskon Lebaran');
            $table->string('kode_diskon', 50)->unique()->comment('Kode unik untuk diskon');
            $table->decimal('persentase_diskon', 5, 2)->comment('Besar persentase diskon');
            $table->date('tanggal_mulai')->comment('Tanggal diskon mulai berlaku');
            $table->date('tanggal_berakhir')->nullable()->comment('Tanggal diskon berakhir (opsional)');
            $table->enum('status_diskon', ['aktif', 'tidak aktif', 'kedaluwarsa'])->default('aktif');
            $table->enum('status_voucher', ['aktif', 'tidak aktif', 'kedaluwarsa'])->default('aktif');
            $table->text('keterangan')->nullable()->comment('Keterangan atau detail tambahan');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('diskon');
    }
};
