<?php

namespace App\Http\Controllers;

use App\Models\KategoriLayanan;
use Illuminate\Http\Request;

class KategoriLayananController extends Controller
{
    // Menampilkan daftar kategori layanan beserta jumlah layanan
    public function index()
    {
        $kategoriLayanan = KategoriLayanan::withCount('layanan')->get(); // Mendapatkan kategori dengan jumlah layanan
        return view('admin.kategori_layanan.index', compact('kategoriLayanan'));
    }

    // Menampilkan form tambah kategori layanan
    public function create()
    {
        return view('admin.kategori_layanan.create');
    }

    // Menyimpan kategori layanan baru
    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'status' => 'required|string|max:35',
            'keterangan' => 'nullable|string',
        ]);

        KategoriLayanan::create([
            'id_admin' => auth()->user()->id,  // Menggunakan id_admin dari user yang sedang login
            'nama' => $request->nama,
            'status' => $request->status,
            'keterangan' => $request->keterangan,
            'tanggal_update' => now(),
        ]);

        session()->flash('message', 'Kategori layanan berhasil ditambahkan!');
        return redirect()->route('kategori-layanan.index');
    }

    // Menampilkan form edit kategori layanan
    public function edit($id)
    {
        $kategoriLayanan = KategoriLayanan::findOrFail($id);
        return view('admin.kategori_layanan.edit', compact('kategoriLayanan'));
    }

    // Mengupdate kategori layanan
    public function update(Request $request, $id)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'status' => 'required|string|max:35',
            'keterangan' => 'nullable|string',
        ]);

        $kategoriLayanan = KategoriLayanan::findOrFail($id);
        $kategoriLayanan->update([
            'nama' => $request->nama,
            'status' => $request->status,
            'keterangan' => $request->keterangan,
            'tanggal_update' => now(),
        ]);

        session()->flash('message', 'Kategori layanan berhasil diperbarui!');
        return redirect()->route('kategori-layanan.index');
    }

    // Menghapus kategori layanan
    public function destroy($id)
    {
        $kategoriLayanan = KategoriLayanan::findOrFail($id);
        $kategoriLayanan->delete();

        session()->flash('message', 'Kategori layanan berhasil dihapus!');
        return redirect()->route('kategori-layanan.index');
    }
}

