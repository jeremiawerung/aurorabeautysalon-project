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
        if (!Schema::hasTable('pembayaran')) {
        Schema::create('pembayaran', function (Blueprint $table) {
            $table->id('id_pembayaran');
            $table->foreignId('id_admin')->nullable()->constrained('admin', 'id_admin')->onDelete('set null')->onUpdate('cascade');
            $table->foreignId('id_reservasi')->nullable()->constrained('reservasi', 'id_reservasi')->onDelete('cascade')->onUpdate('cascade');
            $table->decimal('jumlah', 10, 2);
            $table->string('status_pembayaran', 25);
            $table->foreignId('id_metodePembayaran')->constrained('metodepembayaran', 'id_metodePembayaran')->onDelete('restrict')->onUpdate('cascade');
            $table->dateTime('tanggal_pembayaran');
            $table->text('bukti_pembayaran')->nullable();
            $table->timestamps();
        });
    }
    }

    public function down()
    {
        Schema::dropIfExists('pembayaran');
    }


};
