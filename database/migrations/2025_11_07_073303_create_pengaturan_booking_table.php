<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Perhatikan: Migrasi ini membuat EMPAT tabel terpisah
     * untuk menormalisasi apa yang sebelumnya ada di tabel 'pengaturans'.
     */
    public function up(): void
    {
        // =================================================================
        // Tabel 1: Pengaturan Booking (dari Tab 1)
        // =================================================================
        Schema::create('pengaturan_booking', function (Blueprint $table) {
            $table->id();
            $table->boolean('booking_aktif')->default(true);
            $table->time('jam_mulai')->nullable();
            $table->time('jam_selesai')->nullable();
            $table->boolean('opsi_staff')->default(false);
            $table->string('dp_value')->nullable();
            $table->string('dp_tipe')->default('persen');
            $table->integer('maks_rentang_booking')->default(14);
            $table->integer('interval_min_booking')->default(180);
            $table->text('kebijakan')->nullable();
            $table->timestamps();
        });

        // =================================================================
        // Tabel 2: Tentang Kami (dari Tab 3)
        // =================================================================
        Schema::create('tentang_kami', function (Blueprint $table) {
            $table->id(); // ID ini kemungkinan akan selalu 1 (singleton row)

            $table->text('deskripsi')->nullable(); // Deskripsi tentang perusahaan / sistem
            $table->text('hari_operasional')->nullable(); // Contoh: "Senin - Sabtu"

            $table->timestamps();
        });

        // =================================================================
        // Tabel 3: Lokasi (dari Tab 4)
        // Dibuat tabel agar bisa multi-lokasi di masa depan
        // =================================================================
        Schema::create('lokasi', function (Blueprint $table) {
            $table->id();
            $table->string('nama')->default('Aurora Beauty Salon');
            $table->text('alamat')->nullable();
            $table->string('koordinat')->nullable(); // Simpan lat,long
            $table->boolean('is_utama')->default(false); // Flag untuk lokasi utama

            $table->timestamps();
        });

        // =================================================================
        // Tabel 4: Galeri Foto (Normalisasi dari Tab 2 'image' json)
        // Jauh lebih fleksibel untuk menambah/menghapus/mengurutkan foto
        // =================================================================
        Schema::create('galeri_fotos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_layanan')->constrained('layanan', 'id_layanan')->onDelete('cascade')->onUpdate('cascade');
            $table->string('path_url'); // Path atau URL ke file gambar
            $table->string('keterangan')->nullable(); // Judul atau caption foto
            $table->integer('urutan')->default(0); // Untuk sorting
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * Menghapus semua tabel yang dibuat dalam urutan terbalik
     * untuk keamanan (meskipun dalam kasus ini tidak ada FK antar tabel baru).
     */
    public function down(): void
    {
        Schema::dropIfExists('galeri_fotos');
        Schema::dropIfExists('lokasi');
        Schema::dropIfExists('tentang_kami');
        Schema::dropIfExists('pengaturan_booking');
    }
};
