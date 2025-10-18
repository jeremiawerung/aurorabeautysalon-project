<?php

namespace App\Http\Controllers;

use App\Models\Pelanggan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class PelangganController extends Controller
{
    // Menampilkan daftar pelanggan
    public function index(Request $request)
    {
        $query = Pelanggan::query();

        // Fitur pencarian berdasarkan nama
        if ($request->has('search')) {
            $query->where('nama', 'like', '%' . $request->search . '%');
        }

        // Menggunakan pagination untuk menampilkan data
        $pelanggan = $query->paginate(10);
        return view('admin.pelanggan.index', compact('pelanggan'));
    }

    // Menampilkan form untuk menambah pelanggan
    public function create()
    {
        return view('admin.pelanggan.create');
    }

    // Menyimpan data pelanggan baru
    public function store(Request $request)
    {
        // Validasi input dengan pengecekan unik untuk email dan nomor telepon
        $request->validate([
            'nama' => 'required|string|max:255',
            'email' => 'required|email|unique:pelanggan,email',  // Validasi email unik
            'nomor_telepon' => 'required|numeric|digits_between:10,15|unique:pelanggan,nomor_telepon',  // Validasi nomor telepon unik
            'password' => 'required|min:8',
            'status_pelanggan' => 'required|string',
        ]);

        // Jika validasi berhasil, simpan data pelanggan baru
        Pelanggan::create([
            'nama' => $request->nama,
            'email' => $request->email,
            'nomor_telepon' => $request->nomor_telepon,
            'password' => Hash::make($request->password), // Enkripsi password
            'tanggal_daftar' => now(), // Tanggal daftar
            'status_pelanggan' => $request->status_pelanggan, // Status pelanggan
        ]);

        // Menambahkan pesan konfirmasi setelah pelanggan ditambahkan
        session()->flash('message', 'Pelanggan berhasil ditambahkan!');

        return redirect()->route('pelanggan.index');
    }

    // Menampilkan form untuk mengedit data pelanggan
    public function edit($id)
    {
        $pelanggan = Pelanggan::findOrFail($id);
        return view('admin.pelanggan.edit', compact('pelanggan'));
    }

    // Mengupdate data pelanggan
    public function update(Request $request, $id)
    {
        // Validasi hanya nama yang boleh diubah
        $request->validate([
            'nama' => 'required|string|max:255',
        ]);

        // Menemukan pelanggan berdasarkan ID dan update nama
        $pelanggan = Pelanggan::findOrFail($id);
        $pelanggan->update([
            'nama' => $request->nama,
        ]);

        // Menambahkan pesan konfirmasi setelah nama diperbarui
        session()->flash('message', 'Nama pelanggan berhasil diperbarui!');

        return redirect()->route('pelanggan.index');
    }

    // Menghapus data pelanggan
    public function destroy($id)
    {
        $pelanggan = Pelanggan::findOrFail($id);
        $pelanggan->delete();

        // Menampilkan pesan konfirmasi
        session()->flash('message', 'Pelanggan berhasil dihapus!');
        return redirect()->route('pelanggan.index');
    }
}
