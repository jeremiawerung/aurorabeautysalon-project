<?php

namespace App\Imports;

use App\Models\Pelanggan;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Row;

class PelangganImport implements OnEachRow, WithHeadingRow
{
    protected int $inserted = 0;

    protected int $updated = 0;

    protected int $skipped = 0;

    protected array $errors = [];

    /**
     * Header yang didukung (fleksibel):
     * - nama | name
     * - email
     * - telepon | nomor_telepon | nomor-telepon | nomor telepon | no_telp | no_telepon | hp | phone | telp
     * - status
     * - password (opsional)
     */
    public function onRow(Row $row)
    {
        // Laravel Excel memformat heading menjadi slug (default), jadi "Nomor Telepon" => "nomor_telepon"
        $raw = collect($row->toArray());

        // Lewati baris kosong sepenuhnya tanpa menambah counter
        if ($raw->filter(fn ($v) => filled($v))->isEmpty()) {
            return;
        }

        // Normalisasi trim & case
        $r = $raw->map(fn ($v) => is_string($v) ? trim($v) : $v);

        $nama = $this->firstNonNull($r, ['nama', 'name', 'Nama']);
        $email = $this->lower($this->firstNonNull($r, ['email', 'Email', 'e-mail', 'mail']));

        // Variasi header untuk telepon
        $teleponRaw = $this->firstNonNull($r, [
            'telepon',
            'nomor_telepon',
            'nomor-telepon',
            'nomor telepon',
            'no_telp',
            'no_telepon',
            'no-telepon',
            'hp',
            'phone',
            'telp',
            'Telepon',
            'Nomor Telepon',
        ]);
        $telepon = preg_replace('/\D+/', '', (string) $teleponRaw);

        $status = strtolower((string) ($this->firstNonNull($r, ['status', 'Status']) ?? 'aktif'));
        $passRaw = (string) ($this->firstNonNull($r, ['password', 'Password', 'kata_sandi', 'passwd']) ?? '');

        // Validasi minimal
        if (! $nama || ! $email || ! $telepon) {
            $this->skipped++;
            $this->errors[] = [
                'row' => $row->getIndex(),
                'error' => 'Kolom nama/email/telepon wajib.',
            ];

            return;
        }

        if (! in_array($status, ['aktif', 'non-aktif'], true)) {
            $status = 'aktif';
        }

        $password = $passRaw !== '' ? $passRaw : Str::random(10);

        DB::beginTransaction();
        try {
            // User
            $user = User::where('email', $email)->first();
            if (! $user) {
                $user = User::create([
                    'name' => $nama,
                    'email' => $email,
                    'password' => Hash::make($password),
                    'role' => 'pelanggan',
                    'email_verified_at' => now(),
                ]);
            } else {
                $user->update([
                    'name' => $nama,
                    'password' => $passRaw !== '' ? Hash::make($password) : $user->password,
                ]);
            }

            // Pelanggan
            $pel = Pelanggan::where('email', $email)->first();
            if (! $pel) {
                Pelanggan::create([
                    'nama' => $nama,
                    'email' => $email,
                    'nomor_telepon' => $telepon,
                    'password' => Hash::make($password),
                    'status_pelanggan' => $status,
                    'tanggal_daftar' => now(),
                ]);
                $this->inserted++;
            } else {
                // Validasi unik telepon bila berubah ke milik orang lain
                $dupPhone = Pelanggan::where('nomor_telepon', $telepon)
                    ->where('id_pelanggan', '!=', $pel->id_pelanggan)
                    ->exists();
                if ($dupPhone) {
                    throw ValidationException::withMessages([
                        'telepon' => 'Nomor telepon sudah digunakan pelanggan lain.',
                    ]);
                }

                $pel->update([
                    'nama' => $nama,
                    'email' => $email,
                    'nomor_telepon' => $telepon,
                    'status_pelanggan' => $status,
                    'password' => $passRaw !== '' ? Hash::make($password) : $pel->password,
                ]);
                $this->updated++;
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->skipped++;
            $this->errors[] = [
                'row' => $row->getIndex(),
                'error' => $e->getMessage(),
            ];
        }
    }

    public function getReport(): array
    {
        return [
            'inserted' => $this->inserted,
            'updated' => $this->updated,
            'skipped' => $this->skipped,
            'errors' => $this->errors,
        ];
    }

    // -------- Helpers --------

    /** Ambil nilai pertama yang tidak null/kosong dari beberapa key. */
    private function firstNonNull($collection, array $keys)
    {
        foreach ($keys as $key) {
            $val = $collection->get($key);
            if (! is_null($val) && $val !== '') {
                return $val;
            }
        }

        return null;
    }

    private function lower(?string $s): ?string
    {
        return $s === null ? null : strtolower($s);
    }
}
