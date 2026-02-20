<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('pelanggan', function (Blueprint $table) {
            if (Schema::hasColumn('pelanggan', 'email')) {
                $table->dropColumn('email');
            }
            if (Schema::hasColumn('pelanggan', 'password')) {
                $table->dropColumn('password');
            }
        });

        Schema::table('admin', function (Blueprint $table) {
            if (Schema::hasColumn('admin', 'email')) {
                $table->dropColumn('email');
            }
            if (Schema::hasColumn('admin', 'password')) {
                $table->dropColumn('password');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pelanggan', function (Blueprint $table) {
            $table->string('email')->nullable();
            $table->string('password')->nullable();
        });

        Schema::table('admin', function (Blueprint $table) {
            $table->string('email')->nullable();
            $table->string('password')->nullable();
        });
    }
};
