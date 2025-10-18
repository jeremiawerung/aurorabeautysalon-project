<?php

namespace App\Http\Controllers;

use App\Models\Layanan;
use App\Models\KategoriLayanan;
use App\Models\Slot_Jadwal;
use Illuminate\Http\Request;

class LayananController extends Controller
{
    // Menampilkan daftar layanan
    public function index()
    {
        $layanan = Layanan::with('kategoriLayanan')->get(); // Menampilkan layanan beserta kategori layanan
        return view('admin.layanan.index', compact('layanan'));
    }

    // Menampilkan form tambah layanan
    public function create()
    {
        $kategoriLayanan = KategoriLayanan::all(); // Mengambil semua kategori layanan
        return view('admin.layanan.create', compact('kategoriLayanan'));
    }

    // Menyimpan layanan baru
    public function store(Request $request)
    {
        $request->validate([
            'nama_layanan' => 'required|string|max:255',
            'harga' => 'required|numeric|min:0',
            'deskripsi' => 'nullable|string',
            'durasi' => 'required|integer|min:1',
            'status_layanan' => 'required|in:aktif,nonaktif',
            'id_kategoriLayanan' => 'required|exists:kategori_layanan,id_kategoriLayanan',  // Relasi kategori layanan
        ]);

        Layanan::create([
            'id_admin' => auth()->user()->id,
            'id_kategoriLayanan' => $request->id_kategoriLayanan,
            'nama_layanan' => $request->nama_layanan,
            'harga' => $request->harga,
            'deskripsi' => $request->deskripsi,
            'durasi' => $request->durasi,
            'status_layanan' => $request->status_layanan,
        ]);

        session()->flash('message', 'Layanan berhasil ditambahkan!');
        return redirect()->route('layanan.index');
    }

    // Menampilkan form edit layanan
    public function edit($id)
    {
        $layanan = Layanan::findOrFail($id);
        $kategoriLayanan = KategoriLayanan::all();  // Mengambil kategori layanan
        return view('admin.layanan.edit', compact('layanan', 'kategoriLayanan'));
    }

    // Mengupdate layanan
    public function update(Request $request, $id)
    {
        $request->validate([
            'nama_layanan' => 'required|string|max:255',
            'harga' => 'required|numeric|min:0',
            'deskripsi' => 'nullable|string',
            'durasi' => 'required|integer|min:1',
            'status_layanan' => 'required|in:aktif,nonaktif',
            'id_kategoriLayanan' => 'required|exists:kategori_layanan,id_kategoriLayanan',
        ]);

        $layanan = Layanan::findOrFail($id);
        $layanan->update([
            'id_kategoriLayanan' => $request->id_kategoriLayanan,
            'nama_layanan' => $request->nama_layanan,
            'harga' => $request->harga,
            'deskripsi' => $request->deskripsi,
            'durasi' => $request->durasi,
            'status_layanan' => $request->status_layanan,
        ]);

        session()->flash('message', 'Layanan berhasil diperbarui!');
        return redirect()->route('layanan.index');
    }

    // Menghapus layanan
    public function destroy($id)
    {
        $layanan = Layanan::findOrFail($id);
        $layanan->delete();

        session()->flash('message', 'Layanan berhasil dihapus!');
        return redirect()->route('layanan.index');
    }
}

