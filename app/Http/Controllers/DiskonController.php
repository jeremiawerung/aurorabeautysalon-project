<?php

namespace App\Http\Controllers;

use App\Models\Diskon;
use Illuminate\Http\Request;

class DiskonController extends Controller
{
    // Menampilkan daftar diskon
    public function index()
    {
        $diskons = Diskon::all();  // Mengambil semua diskon
        return view('admin.diskon.index', compact('diskons'));
    }

    // Menampilkan form tambah diskon
    public function create()
    {
        return view('admin.diskon.create');
    }

    // Menyimpan diskon baru
     public function store(Request $request)
        {
            // Validasi input
            $request->validate([
                'nama_diskon' => 'required|string|max:255',
                'persentase_diskon' => 'required|numeric|min:0|max:100',
                'tanggal_mulai' => 'required|date',
                'tanggal_berakhir' => 'required|date|after_or_equal:tanggal_mulai',
                'status' => 'required|string|in:aktif,nonaktif',
                'keterangan' => 'nullable|string',
            ]);

            // Menyimpan diskon baru
            $diskon = Diskon::create([
                'nama_diskon' => $request->nama_diskon,
                'persentase_diskon' => $request->persentase_diskon,
                'tanggal_mulai' => $request->tanggal_mulai,
                'tanggal_berakhir' => $request->tanggal_berakhir,
                'status' => $request->status,
                'keterangan' => $request->keterangan,
            ]);

            // Menambahkan pesan konfirmasi setelah diskon ditambahkan
            session()->flash('message', 'Diskon berhasil ditambahkan!');

            // Pastikan redirect ke halaman daftar diskon setelah berhasil disimpan
            return redirect()->route('diskon.index');  // Mengarahkan ke daftar diskon
        }



    // Menampilkan form edit diskon
    public function edit($id)
    {
        $diskon = Diskon::findOrFail($id);
        return view('admin.diskon.edit', compact('diskon'));
    }

    // Mengupdate diskon
    public function update(Request $request, $id)
    {
        $request->validate([
            'nama_diskon' => 'required|string|max:255',
            'persentase_diskon' => 'required|numeric|min:0|max:100',
            'tanggal_mulai' => 'required|date',
            'tanggal_berakhir' => 'required|date|after_or_equal:tanggal_mulai',
            'status' => 'required|string|in:aktif,nonaktif',
            'keterangan' => 'nullable|string',
        ]);

        $diskon = Diskon::findOrFail($id);
        $diskon->update([
            'nama_diskon' => $request->nama_diskon,
            'persentase_diskon' => $request->persentase_diskon,
            'tanggal_mulai' => $request->tanggal_mulai,
            'tanggal_berakhir' => $request->tanggal_berakhir,
            'status' => $request->status,
            'keterangan' => $request->keterangan,
        ]);

        session()->flash('message', 'Diskon berhasil diperbarui!');
        return redirect()->route('diskon.index');
    }

    // Menghapus diskon
    public function destroy($id)
    {
        $diskon = Diskon::findOrFail($id);
        $diskon->delete();

        session()->flash('message', 'Diskon berhasil dihapus!');
        return redirect()->route('diskon.index');
    }
}
