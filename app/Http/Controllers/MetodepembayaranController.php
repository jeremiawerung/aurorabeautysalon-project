<?php

namespace App\Http\Controllers;

use App\Models\MetodePembayaran;
use Illuminate\Http\Request;

class MetodePembayaranController extends Controller
{
    // Menampilkan daftar metode pembayaran
    public function index()
    {
        $metodePembayaran = MetodePembayaran::with('admin')->get(); // Mengambil semua metode pembayaran
        return view('admin.metode_pembayaran.index', compact('metodePembayaran'));
    }

    // Menampilkan form tambah metode pembayaran
    public function create()
    {
        return view('admin.metode_pembayaran.create');
    }

    // Menyimpan metode pembayaran baru
    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'status' => 'required|string|max:35',
            'keterangan' => 'nullable|string',
        ]);

        MetodePembayaran::create([
            'id_admin' => auth()->user()->id,  // Mengambil ID admin yang sedang login
            'nama' => $request->nama,
            'status' => $request->status,
            'keterangan' => $request->keterangan,
            'tanggal_dibuat' => now(),
            'tanggal_update' => now(),
        ]);

        session()->flash('message', 'Metode pembayaran berhasil ditambahkan!');
        return redirect()->route('metode-pembayaran.index');
    }

    // Menampilkan form edit metode pembayaran
    public function edit($id)
    {
        $metodePembayaran = MetodePembayaran::findOrFail($id);
        return view('admin.metode_pembayaran.edit', compact('metodePembayaran'));
    }

    // Mengupdate metode pembayaran
    public function update(Request $request, $id)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'status' => 'required|string|max:35',
            'keterangan' => 'nullable|string',
        ]);

        $metodePembayaran = MetodePembayaran::findOrFail($id);
        $metodePembayaran->update([
            'nama' => $request->nama,
            'status' => $request->status,
            'keterangan' => $request->keterangan,
            'tanggal_update' => now(),
        ]);

        session()->flash('message', 'Metode pembayaran berhasil diperbarui!');
        return redirect()->route('metode-pembayaran.index');
    }

    // Menghapus metode pembayaran
    public function destroy($id)
    {
        $metodePembayaran = MetodePembayaran::findOrFail($id);
        $metodePembayaran->delete();

        session()->flash('message', 'Metode pembayaran berhasil dihapus!');
        return redirect()->route('metode-pembayaran.index');
    }
}

