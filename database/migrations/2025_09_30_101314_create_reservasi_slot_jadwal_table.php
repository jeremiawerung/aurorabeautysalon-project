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
        if (!Schema::hasTable('reservasi_slot_jadwal')) {
        Schema::create('reservasi_slot_jadwal', function (Blueprint $table) {
            $table->foreignId('id_reservasi')->constrained('reservasi')->onDelete('cascade');
            $table->foreignId('id_slot')->constrained('slot_jadwal')->onDelete('cascade');
            $table->primary(['id_reservasi', 'id_slot']);
            $table->timestamps();
        });
    }
    }

    public function down()
    {
        Schema::dropIfExists('reservasi_slot_jadwal');
    }


};
