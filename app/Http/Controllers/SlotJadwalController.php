<?php

namespace App\Http\Controllers;

use App\Models\Slot_jadwal;
use App\Models\Layanan;
use Illuminate\Http\Request;

class SlotJadwalController extends Controller
{
    // Menampilkan daftar slot jadwal
    public function index()
    {
        $slotJadwal = Slot_jadwal::with('layanan')->get(); // Mengambil semua slot jadwal dengan relasi layanan
        return view('admin.slot_jadwal.index', compact('slotJadwal'));
    }

    // Menampilkan form tambah slot jadwal
    public function create()
    {
        $layanan = Layanan::all(); // Mengambil semua layanan untuk dropdown
        return view('admin.slot_jadwal.create', compact('layanan'));
    }

    // Menyimpan slot jadwal baru
    public function store(Request $request)
    {
        $request->validate([
            'id_layanan' => 'required|exists:layanan,id_layanan',  // Validasi id_layanan yang ada di tabel layanan
            'waktu' => 'required|date_format:H:i',  // Format waktu
            'status_slot' => 'required|string',
        ]);

        Slot_jadwal::create([
            'id_admin' => auth()->user()->id,  // Mengambil ID admin yang sedang login
            'id_layanan' => $request->id_layanan,
            'waktu' => $request->waktu,
            'status_slot' => $request->status_slot,
            'is_default' => $request->is_default ?? 1,  // Default: 1 jika tidak diatur
        ]);

        session()->flash('message', 'Slot jadwal berhasil ditambahkan!');
        return redirect()->route('slot-jadwal.index');
    }

    // Menampilkan form edit slot jadwal
    public function edit($id)
    {
        $slotJadwal = Slot_jadwal::findOrFail($id);
        $layanan = Layanan::all();  // Mengambil semua layanan
        return view('admin.slot_jadwal.edit', compact('slotJadwal', 'layanan'));
    }

    // Mengupdate slot jadwal
    public function update(Request $request, $id)
    {
        $request->validate([
            'id_layanan' => 'required|exists:layanan,id_layanan',
            'waktu' => 'required|date_format:H:i',
            'status_slot' => 'required|string',
        ]);

        $slotJadwal = Slot_jadwal::findOrFail($id);
        $slotJadwal->update([
            'id_layanan' => $request->id_layanan,
            'waktu' => $request->waktu,
            'status_slot' => $request->status_slot,
            'is_default' => $request->is_default ?? 1,
        ]);

        session()->flash('message', 'Slot jadwal berhasil diperbarui!');
        return redirect()->route('slot-jadwal.index');
    }

    // Menghapus slot jadwal
    public function destroy($id)
    {
        $slotJadwal = Slot_jadwal::findOrFail($id);
        $slotJadwal->delete();

        session()->flash('message', 'Slot jadwal berhasil dihapus!');
        return redirect()->route('slot-jadwal.index');
    }
}

