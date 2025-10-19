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
        if (!Schema::hasTable('reservasi')) {
            Schema::create('reservasi', function (Blueprint $table) {
                $table->id('id_reservasi');
                $table->foreignId('id_pelanggan')->constrained('pelanggan', 'id_pelanggan')->onDelete('cascade')->onUpdate('cascade');
                $table->date('tanggal_reservasi');
                $table->time('waktu_reservasi');
                $table->string('status_reservasi', 25)->default('pending');
                $table->text('catatan')->nullable();
                $table->decimal('total_harga', 10, 2)->default(0);
                $table->timestamps();
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('reservasi');
    }
};