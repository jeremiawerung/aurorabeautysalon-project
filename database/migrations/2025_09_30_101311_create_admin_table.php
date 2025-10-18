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
        if (!Schema::hasTable('admin')) {
            Schema::create('admin', function (Blueprint $table) {
                $table->id('id_admin');
                $table->string('nama', 60);
                $table->string('email', 65)->unique();
                $table->string('password');
                $table->dateTime('last_login')->nullable();
                $table->string('status_admin', 35)->default('aktif');
                // Pastikan status_admin boleh NULL
                $table->timestamps();  // Untuk created_at dan updated_at
            });
        }
    }


    public function down()
    {
        Schema::dropIfExists('admin');
    }



};
