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
        if (!Schema::hasTable('pilihan_layanan')) {
        Schema::create('pilihan_layanan', function (Blueprint $table) {
            $table->id('id_pilihan');
            $table->foreignId('id_pelanggan')->constrained('pelanggan')->onDelete('cascade')->onUpdate('cascade');
            $table->foreignId('id_slot')->constrained('slot_jadwal')->onDelete('cascade')->onUpdate('cascade');
            $table->date('tanggal_dipilih');
            $table->string('status_pilihan', 25);
            $table->timestamps();
        });
    }
    }

    public function down()
    {
        Schema::dropIfExists('pilihan_layanan');
    }


};
