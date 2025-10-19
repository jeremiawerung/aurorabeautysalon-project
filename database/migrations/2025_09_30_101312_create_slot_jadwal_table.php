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
        if (!Schema::hasTable('slot_jadwal')) {
        Schema::create('slot_jadwal', function (Blueprint $table) {
            $table->id('id_slot');
            $table->foreignId('id_admin')->nullable()->constrained('admin', 'id_admin')->onDelete('set null')->onUpdate('cascade');
            $table->foreignId('id_layanan')->nullable()->constrained('layanan', 'id_layanan')->onDelete('cascade')->onUpdate('cascade');
            $table->time('waktu');
            $table->string('status_slot', 25);
            $table->tinyInteger('is_default')->default(1);
            $table->timestamps();
        });
    }
    }

    public function down()
    {
        Schema::dropIfExists('slot_jadwal');
    }


};
