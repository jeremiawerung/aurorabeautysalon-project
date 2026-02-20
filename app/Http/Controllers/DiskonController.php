<?php

namespace App\Http\Controllers;

use App\Models\Diskon;
use App\Models\Layanan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class DiskonController extends Controller
{
    public function index()
    {
        $diskons = Diskon::all();

        return view('admin.diskon.index', compact('diskons'));
    }

    public function create()
    {
        // Ambil semua layanan untuk ditampilkan di dropdown
        $layanans = Layanan::select('id_layanan', 'nama_layanan', 'harga')
            ->whereNull('id_diskon') // Hanya tampilkan layanan yang belum punya diskon
            ->get();

        return view('admin.diskon.create', compact('layanans'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'kode_diskon' => 'required|string|max:50|unique:diskon,kode_diskon',
            'nama_diskon' => 'required|string|max:255',
            'persentase_diskon' => 'required|numeric|min:0|max:100',
            'tanggal_mulai' => 'required|date',
            'tanggal_berakhir' => 'nullable|date|after_or_equal:tanggal_mulai',
            'status_diskon' => 'required|string|in:aktif,tidak aktif,kedaluwarsa',
            'keterangan' => 'nullable|string',
            'layanan_ids' => 'nullable|array',
            'layanan_ids.*' => 'exists:layanan,id_layanan',
        ]);

        try {
            // LANGKAH 1: Buat Diskon terlebih dahulu
            $diskon = Diskon::create([
                'kode_diskon' => strtoupper($request->kode_diskon),
                'nama_diskon' => $request->nama_diskon,
                'persentase_diskon' => $request->persentase_diskon,
                'tanggal_mulai' => $request->tanggal_mulai,
                'tanggal_berakhir' => $request->tanggal_berakhir,
                'status_diskon' => $request->status_diskon,
                'status_voucher' => $request->status_diskon, // Samakan nilainya
                'keterangan' => $request->keterangan,
            ]);

            // LANGKAH 2: Jika ada layanan yang dipilih, update tabel layanan
            if ($request->has('layanan_ids') && ! empty($request->layanan_ids)) {
                Layanan::whereIn('id_layanan', $request->layanan_ids)
                    ->update(['id_diskon' => $diskon->id]);
            }

            Session::flash('success', 'Diskon baru berhasil ditambahkan! Kode: '.strtoupper($request->kode_diskon));

            return redirect()->route('diskon.index');

        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Gagal menyimpan diskon: '.$e->getMessage());
        }
    }

    public function edit(Diskon $diskon)
    {
        // Ambil layanan yang sudah menggunakan diskon ini
        $layananTerpilih = Layanan::where('id_diskon', $diskon->id)
            ->pluck('id_layanan')
            ->toArray();

        // Ambil semua layanan (yang belum punya diskon + yang sudah pakai diskon ini)
        $layanans = Layanan::select('id_layanan', 'nama_layanan', 'harga')
            ->where(function ($query) use ($diskon) {
                $query->whereNull('id_diskon')
                    ->orWhere('id_diskon', $diskon->id);
            })
            ->get();

        return view('admin.diskon.edit', compact('diskon', 'layanans', 'layananTerpilih'));
    }

    public function update(Request $request, Diskon $diskon)
    {
        $request->validate([
            'kode_diskon' => 'required|string|max:50|unique:diskon,kode_diskon,'.$diskon->id,
            'nama_diskon' => 'required|string|max:255',
            'persentase_diskon' => 'required|numeric|min:0|max:100',
            'tanggal_mulai' => 'required|date',
            'tanggal_berakhir' => 'nullable|date|after_or_equal:tanggal_mulai',
            'status_diskon' => 'required|string|in:aktif,tidak aktif,kedaluwarsa',
            'keterangan' => 'nullable|string',
            'layanan_ids' => 'nullable|array',
            'layanan_ids.*' => 'exists:layanan,id_layanan',
        ]);

        try {
            // Update data diskon
            $diskon->update([
                'kode_diskon' => strtoupper($request->kode_diskon),
                'nama_diskon' => $request->nama_diskon,
                'persentase_diskon' => $request->persentase_diskon,
                'tanggal_mulai' => $request->tanggal_mulai,
                'tanggal_berakhir' => $request->tanggal_berakhir,
                'status_diskon' => $request->status_diskon,
                'status_voucher' => $request->status_diskon,
                'keterangan' => $request->keterangan,
            ]);

            // Reset semua layanan yang sebelumnya menggunakan diskon ini
            Layanan::where('id_diskon', $diskon->id)
                ->update(['id_diskon' => null]);

            // Set layanan baru yang dipilih
            if ($request->has('layanan_ids') && ! empty($request->layanan_ids)) {
                Layanan::whereIn('id_layanan', $request->layanan_ids)
                    ->update(['id_diskon' => $diskon->id]);
            }

            Session::flash('success', 'Diskon dengan kode '.$diskon->kode_diskon.' berhasil diperbarui!');

            return redirect()->route('diskon.index');

        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Gagal memperbarui diskon: '.$e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $diskon = Diskon::findOrFail($id);

            // Reset id_diskon di tabel layanan sebelum menghapus diskon
            Layanan::where('id_diskon', $diskon->id)
                ->update(['id_diskon' => null]);

            $diskon->delete();

            session()->flash('success', 'Diskon berhasil dihapus!');
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal menghapus diskon: '.$e->getMessage());
        }

        return redirect()->route('diskon.index');
    }
}
