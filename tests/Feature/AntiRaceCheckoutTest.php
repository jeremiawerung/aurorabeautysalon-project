<?php

namespace Tests\Feature;

use App\Models\Reservasi;
use App\Models\User;
use App\Support\SlotReservasi;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Test anti race condition checkout (audit 17 Okt 2026):
 * slot rebutan, slot ditahan pas bayar, reservasi batal, dan pembayaran dobel.
 */
class AntiRaceCheckoutTest extends TestCase
{
    use RefreshDatabase;

    private string $tanggal;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tanggal = now()->addDays(3)->toDateString();
    }

    private function buatLayanan(string $nama = 'Manicure'): int
    {
        $idLayanan = DB::table('layanan')->insertGetId([
            'nama_layanan' => $nama,
            'harga' => 100000,
            'durasi' => 60,
            'status_layanan' => 'aktif',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('slot_jadwal')->insert([
            'id_layanan' => $idLayanan,
            'waktu' => '10:00:00',
            'status_slot' => 'tersedia',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $idLayanan;
    }

    private function buatPelanggan(): array
    {
        $user = User::factory()->create();
        $idPelanggan = DB::table('pelanggan')->insertGetId([
            'user_id' => $user->id,
            'nama' => 'Pelanggan Uji',
            'nomor_telepon' => '081200000000',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return [$user, $idPelanggan];
    }

    private function buatReservasi(int $idPelanggan, int $idLayanan, string $status = 'diproses', array $tambahan = []): int
    {
        $idReservasi = DB::table('reservasi')->insertGetId(array_merge([
            'id_pelanggan' => $idPelanggan,
            'tanggal_reservasi' => $this->tanggal,
            'waktu_reservasi' => '10:00:00',
            'status_reservasi' => $status,
            'total_harga' => 100000,
            'created_at' => now(),
            'updated_at' => now(),
        ], $tambahan));

        DB::table('reservasi_layanan')->insert([
            'id_reservasi' => $idReservasi,
            'id_layanan' => $idLayanan,
            'harga_deal' => 100000,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $idReservasi;
    }

    private function buatPembayaran(int $idReservasi, string $orderId, string $status = 'bayar_dp'): void
    {
        $idMetode = DB::table('metodepembayaran')->value('id_metodePembayaran')
            ?? DB::table('metodepembayaran')->insertGetId([
                'nama' => 'Midtrans',
                'status' => 'aktif',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

        DB::table('pembayaran')->insert([
            'id_reservasi' => $idReservasi,
            'id_metodePembayaran' => $idMetode,
            'jumlah' => 30000,
            'status_pembayaran' => $status,
            'tanggal_pembayaran' => now(),
            'order_id' => $orderId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function test_slot_kosong_gak_bentrok(): void
    {
        $idLayanan = $this->buatLayanan();

        $this->assertFalse(SlotReservasi::bentrok($idLayanan, $this->tanggal, '10:00'));
    }

    public function test_slot_yang_lagi_ditahan_orang_lain_bentrok(): void
    {
        $idLayanan = $this->buatLayanan();
        [, $idPelanggan] = $this->buatPelanggan();
        $this->buatReservasi($idPelanggan, $idLayanan, 'pending', ['ditahan_sampai' => now()->addMinutes(10)]);

        $this->assertTrue(SlotReservasi::bentrok($idLayanan, $this->tanggal, '10:00'));
    }

    public function test_tahanan_yang_udah_kadaluarsa_gak_bentrok(): void
    {
        $idLayanan = $this->buatLayanan();
        [, $idPelanggan] = $this->buatPelanggan();
        $this->buatReservasi($idPelanggan, $idLayanan, 'pending', ['ditahan_sampai' => now()->subMinute()]);

        $this->assertFalse(SlotReservasi::bentrok($idLayanan, $this->tanggal, '10:00'));
    }

    public function test_slot_yang_udah_dibayar_bentrok_tapi_gak_sama_reservasinya_sendiri(): void
    {
        $idLayanan = $this->buatLayanan();
        [, $idPelanggan] = $this->buatPelanggan();
        $idReservasi = $this->buatReservasi($idPelanggan, $idLayanan, 'pending');
        $this->buatPembayaran($idReservasi, 'TRX-MULTI-1-'.$idReservasi);

        $this->assertTrue(SlotReservasi::bentrok($idLayanan, $this->tanggal, '10:00'));
        $this->assertFalse(SlotReservasi::bentrok($idLayanan, $this->tanggal, '10:00', $idReservasi));
    }

    public function test_layanan_lain_di_jam_yang_sama_gak_bikin_bentrok(): void
    {
        // Dulu orWhere gak dibungkus, jadi reservasi layanan lain yang statusnya 'proses' ikut keitung
        $idManicure = $this->buatLayanan('Manicure');
        $idCreambath = $this->buatLayanan('Creambath');
        [, $idPelanggan] = $this->buatPelanggan();
        $this->buatReservasi($idPelanggan, $idCreambath, 'proses');

        $this->assertFalse(SlotReservasi::bentrok($idManicure, $this->tanggal, '10:00'));
    }

    public function test_bayar_slot_yang_lagi_dibayar_orang_lain_ditolak(): void
    {
        $idLayanan = $this->buatLayanan();
        [, $idPelangganA] = $this->buatPelanggan();
        [$userB, $idPelangganB] = $this->buatPelanggan();

        // A udah klik Bayar duluan, slotnya ditahan
        $this->buatReservasi($idPelangganA, $idLayanan, 'pending', ['ditahan_sampai' => now()->addMinutes(30)]);
        $idReservasiB = $this->buatReservasi($idPelangganB, $idLayanan);

        $respon = $this->actingAs($userB)->postJson(route('booking.midtrans.proses'), [
            'pay_type' => 'dp',
            'reservasi_ids' => [$idReservasiB],
        ]);

        $respon->assertStatus(409)->assertJson(['success' => false]);
        $this->assertStringContainsString('baru saja diambil pelanggan lain', $respon->json('message'));
        $this->assertNull(Reservasi::find($idReservasiB)->ditahan_sampai);
    }

    public function test_reservasi_yang_udah_dibatalkan_gak_bisa_dibayar(): void
    {
        $idLayanan = $this->buatLayanan();
        [$user, $idPelanggan] = $this->buatPelanggan();
        $idReservasi = $this->buatReservasi($idPelanggan, $idLayanan, 'dibatalkan');

        $this->actingAs($user)->postJson(route('booking.midtrans.proses'), [
            'pay_type' => 'full',
            'reservasi_ids' => [$idReservasi],
        ])->assertStatus(404);

        $this->assertSame('dibatalkan', Reservasi::find($idReservasi)->status_reservasi);
    }

    public function test_tahan_untuk_checkout_nahan_slot_kalau_aman(): void
    {
        $idLayanan = $this->buatLayanan();
        [, $idPelanggan] = $this->buatPelanggan();
        $idReservasi = $this->buatReservasi($idPelanggan, $idLayanan);

        $bentrok = SlotReservasi::tahanUntukCheckout(Reservasi::with('reservasiLayanan')->whereKey($idReservasi)->get());

        $this->assertNull($bentrok);
        $this->assertTrue(Reservasi::find($idReservasi)->ditahan_sampai > now());
    }

    public function test_order_id_pembayaran_gak_bisa_dobel(): void
    {
        $idLayanan = $this->buatLayanan();
        [, $idPelanggan] = $this->buatPelanggan();
        $idReservasi = $this->buatReservasi($idPelanggan, $idLayanan, 'pending');
        $this->buatPembayaran($idReservasi, 'TRX-MULTI-9-'.$idReservasi);

        $this->expectException(UniqueConstraintViolationException::class);
        $this->buatPembayaran($idReservasi, 'TRX-MULTI-9-'.$idReservasi);
    }
}
