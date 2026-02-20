<?php

namespace App\Http\Controllers;

use App\Exports\PelangganExport;
use App\Imports\PelangganImport;
use App\Models\Diskon;
use App\Models\MetodePembayaran;
use App\Models\Pelanggan;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class PelangganController extends Controller
{
    public function index()
    {
        $metodePembayaran = MetodePembayaran::all();
        return view('admin.pelanggan.index', compact('metodePembayaran'));
    }

    public function export()
    {
        $filename = 'pelanggan-'.now()->format('Ymd-His').'.xlsx';

        return Excel::download(new PelangganExport, $filename);
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,csv,xls|max:20480', // 20 MB
        ]);

        try {
            $importer = new PelangganImport;
            Excel::import($importer, $request->file('file'));

            $report = $importer->getReport(); // ['inserted'=>x,'updated'=>y,'skipped'=>z,'errors'=>[...]]
            $msg = "Import selesai. Inserted: {$report['inserted']}, Updated: {$report['updated']}, Skipped: {$report['skipped']}.";
            if (! empty($report['errors'])) {
                $msg .= ' Ada error pada beberapa baris.';
                // Kamu bisa simpan ke log juga
                Log::warning('Pelanggan import errors', $report['errors']);
            }

            return redirect()->route('pelanggan.index')->with('success', $msg);
        } catch (\Throwable $e) {
            Log::error('Import pelanggan gagal: '.$e->getMessage());

            return back()->with('error', 'Gagal import: '.$e->getMessage());
        }
    }

    public function data_pelanggan()
    {
        return view('admin.pelanggan.data-pelanggan');
    }

    public function ajax()
    {
        try {
            $pelanggan = Pelanggan::query()
                ->join('users', 'pelanggan.user_id', '=', 'users.id')
                ->where('users.role', 'pelanggan')
                ->select('pelanggan.*', 'users.name as user_name', 'users.email as email', 'users.id as user_id')
                ->orderBy('pelanggan.id_pelanggan', 'desc')
                ->get()
                ->map(function ($item) {
                    $nomor = $item->nomor_telepon;
                    if (! empty($nomor) && strlen($nomor) >= 10) {
                        $nomor = preg_replace("/^(\d{4})(\d{4})(\d+)$/", '$1-$2-$3', $nomor);
                    }
                    $item->formatted_nomor = $nomor;

                    $item->formatted_tanggal_daftar = ! empty($item->tanggal_daftar)
                        ? Carbon::parse($item->tanggal_daftar)->translatedFormat('d F Y')
                        : '-';

                    $item->formatted_status = ucfirst(strtolower($item->status_pelanggan ?? 'Tidak Diketahui'));

                    return $item;
                });

            return response()->json([
                'success' => true,
                'data' => $pelanggan,
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching pelanggan AJAX: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat data pelanggan.',
            ], 500);
        }
    }

    public function create()
    {
        return view('admin.pelanggan.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'telepon' => 'required|numeric|digits_between:10,15|unique:pelanggan,nomor_telepon',
            'password' => 'required|min:8|confirmed',
            'status' => 'required|string|in:aktif,non-aktif',
        ]);

        try {
            DB::beginTransaction();

            $user = User::create([
                'name' => $validated['nama'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'role' => 'pelanggan',
                'email_verified_at' => now(),
            ]);

            Pelanggan::create([
                'user_id' => $user->id,
                'nama' => $validated['nama'],
                'nomor_telepon' => $validated['telepon'],
                'status_pelanggan' => $validated['status'],
                'tanggal_daftar' => now(),
            ]);

            DB::commit();

            return redirect()->route('pelanggan.data_pelanggan')
                ->with('success', 'Pelanggan berhasil ditambahkan dan email sudah diverifikasi!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal menambahkan pelanggan: '.$e->getMessage());

            return back()->withInput()->with('error', 'Terjadi kesalahan saat menambahkan pelanggan.');
        }
    }

    public function edit($id)
    {
        $pelanggan = Pelanggan::findOrFail($id);

        return view('admin.pelanggan.edit', compact('pelanggan'));
    }

    public function update(Request $request, $id)
    {
        $pelanggan = Pelanggan::findOrFail($id);
        $user = User::find($pelanggan->user_id);

        $request->validate([
            'nama' => 'required|string|max:255',
            'email' => 'required|email',
            'telepon' => 'required|numeric|digits_between:10,15',
            'password' => 'nullable|min:8|confirmed',
            'status' => 'required|string|in:aktif,non-aktif',
        ]);

        $cekEmail = User::where('email', $request->email)
            ->where('id', '!=', $user?->id)
            ->exists();

        if ($cekEmail) {
            return back()->withInput()->with('error', 'Email sudah digunakan oleh pengguna lain.');
        }

        $cekTelepon = Pelanggan::where('nomor_telepon', $request->telepon)
            ->where('id_pelanggan', '!=', $pelanggan->id_pelanggan)
            ->exists();

        if ($cekTelepon) {
            return back()->withInput()->with('error', 'Nomor telepon sudah digunakan.');
        }

        try {
            DB::beginTransaction();

            if ($user) {
                $user->update([
                    'name' => $request->nama,
                    'email' => $request->email,
                    'password' => ! empty($request->password)
                        ? Hash::make($request->password)
                        : $user->password,
                ]);
            }

            $pelanggan->update([
                'nama' => $request->nama,
                'nomor_telepon' => $request->telepon,
                'status_pelanggan' => $request->status,
            ]);

            DB::commit();

            return redirect()->route('pelanggan.data_pelanggan')
                ->with('success', 'Data pelanggan berhasil diperbarui!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal memperbarui pelanggan: '.$e->getMessage());

            return back()->withInput()->with('error', 'Terjadi kesalahan saat memperbarui data pelanggan.');
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();

        try {
            $pelanggan = Pelanggan::findOrFail($id);
            $user = User::find($pelanggan->user_id);

            if ($user && Auth::check() && Auth::id() === $user->id) {
                Auth::logout();
                session()->invalidate();
                session()->regenerateToken();
            }

            if ($user) {
                $user->delete();
            }

            $pelanggan->delete();

            DB::commit();

            return redirect()->route('pelanggan.data_pelanggan')
                ->with('success', 'Pelanggan dan akun user berhasil dihapus!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal menghapus pelanggan: '.$e->getMessage());

            return back()->withErrors(['error' => 'Terjadi kesalahan saat menghapus pelanggan.']);
        }
    }

    public function myDiskon()
    {
        $user = Auth::user();
        $pelanggan = Pelanggan::where('user_id', $user->id)->firstOrFail();

        $diskons = Diskon::where(function ($query) use ($pelanggan) {
            $query->where('tipe_diskon', 'GLOBAL')
                ->orWhere(function ($q) use ($pelanggan) {
                    $q->where('tipe_diskon', 'PERSONAL')
                        ->where('pelanggan_id', $pelanggan->id_pelanggan);
                });
        })
            ->whereIn('status', ['aktif', 'tidak aktif', 'kedaluwarsa']) // Semua status
            ->orderBy('created_at', 'desc')
            ->get();

        return view('pelanggan.diskon', compact('diskons', 'pelanggan'));
    }
}
