<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('reservasi_layanan', function (Blueprint $table) {
            $table->decimal('harga_deal', 12, 2)->nullable()->after('id_layanan');
            $table->string('nama_layanan_snapshot')->nullable()->after('harga_deal');
        });

        // Migrate Existing Data
        $data = DB::table('reservasi_layanan')
            ->join('layanan', 'reservasi_layanan.id_layanan', '=', 'layanan.id_layanan')
            ->select('reservasi_layanan.id_reservasi', 'reservasi_layanan.id_layanan', 'layanan.harga', 'layanan.nama_layanan')
            ->get();

        foreach ($data as $row) {
            DB::table('reservasi_layanan')
                ->where('id_reservasi', $row->id_reservasi)
                ->where('id_layanan', $row->id_layanan)
                ->update([
                    'harga_deal' => $row->harga,
                    'nama_layanan_snapshot' => $row->nama_layanan
                ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('reservasi_layanan', function (Blueprint $table) {
            $table->dropColumn(['harga_deal', 'nama_layanan_snapshot']);
        });
    }
};
