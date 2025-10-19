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
        if (!Schema::hasTable('reservasi_layanan')) {
        Schema::create('reservasi_layanan', function (Blueprint $table) {
            $table->foreignId('id_reservasi')->constrained('reservasi', 'id_reservasi')->onDelete('cascade')->onUpdate('cascade');
            $table->foreignId('id_layanan')->constrained('layanan', 'id_layanan')->onDelete('cascade')->onUpdate('cascade');
            $table->primary(['id_reservasi', 'id_layanan']);
            $table->timestamps();
        });
    }
    }

    public function down()
    {
        Schema::dropIfExists('reservasi_layanan');
    }


};
