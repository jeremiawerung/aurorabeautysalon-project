<?php

namespace App\Http\Controllers;

use App\Models\Reservasi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    private function getCurrentPelanggan()
    {
        $user = Auth::user();

        if (! $user) {
            return null;
        }

        return $user->pelanggan;
    }

    public function index()
    {
        $user = Auth::user();
        $pelanggan = $this->getCurrentPelanggan();

        if (! $pelanggan) {
            return redirect()
                ->route('home')
                ->with('error', 'Data pelanggan tidak ditemukan untuk akun ini.');
        }

        $totalReservasi = Reservasi::where('id_pelanggan', $pelanggan->id_pelanggan)->count();

        return view('pelanggan.profile', [
            'user' => $user,
            'pelanggan' => $pelanggan,
            'totalReservasi' => $totalReservasi,
        ]);
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'password' => 'required|string|min:8|confirmed',
        ], [
            'password.required' => 'Password baru wajib diisi.',
            'password.min' => 'Password minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
        ]);

        $user = Auth::user();

        $user->password = Hash::make($request->password);
        $user->save();

        return redirect()
            ->route('pelanggan.profile')
            ->with('success', 'Password berhasil diperbarui.');
    }
}
