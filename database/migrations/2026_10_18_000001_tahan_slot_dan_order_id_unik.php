<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Anti race condition checkout:
     * - reservasi.ditahan_sampai: slot dipegang reservasi ini selama pelanggan lagi bayar di Midtrans
     * - pembayaran.order_id unik: callback browser + webhook Midtrans gak bisa nyatet pembayaran yang sama 2x
     */
    public function up(): void
    {
        Schema::table('reservasi', function (Blueprint $table) {
            $table->dateTime('ditahan_sampai')->nullable()->after('status_reservasi');
        });

        Schema::table('pembayaran', function (Blueprint $table) {
            $table->unique('order_id', 'pembayaran_order_id_unique');
        });
    }

    public function down(): void
    {
        Schema::table('pembayaran', function (Blueprint $table) {
            $table->dropUnique('pembayaran_order_id_unique');
        });

        Schema::table('reservasi', function (Blueprint $table) {
            $table->dropColumn('ditahan_sampai');
        });
    }
};
