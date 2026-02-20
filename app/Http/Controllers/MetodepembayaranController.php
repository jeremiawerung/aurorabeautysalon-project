<?php

namespace App\Http\Controllers;

use App\Models\MetodePembayaran;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class MetodePembayaranController extends Controller
{
    public function index()
    {
        return view('admin.metode_pembayaran.index');
    }

    public function ajax()
    {
        try {
            $data = MetodePembayaran::orderBy('tanggal_dibuat', 'desc')->get();

            return response()->json([
                'success' => true,
                'data' => $data,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat data metode pembayaran.',
            ], 500);
        }
    }

    public function create()
    {
        return view('admin.metode_pembayaran.create');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama' => 'required|string|max:60',
            'status' => 'required|string|max:35',
            'keterangan' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        DB::beginTransaction();
        try {
            MetodePembayaran::create([
                'nama' => $request->nama,
                'status' => $request->status,
                'keterangan' => $request->keterangan,
                'tanggal_dibuat' => now(),
                'tanggal_update' => now(),
            ]);

            DB::commit();

            return redirect()->route('metode-pembayaran.index')
                ->with('success', 'Metode pembayaran berhasil ditambahkan!');
        } catch (Exception $e) {
            DB::rollBack();

            return redirect()->back()
                ->with('error', 'Terjadi kesalahan saat menyimpan data.')
                ->withInput();
        }
    }

    public function edit($id)
    {
        $metodePembayaran = MetodePembayaran::where('id_metodePembayaran', $id)->firstOrFail();

        return view('admin.metode_pembayaran.edit', compact('metodePembayaran'));
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'nama' => 'required|string|max:60',
            'status' => 'required|string|max:35',
            'keterangan' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        DB::beginTransaction();
        try {
            $metodePembayaran = MetodePembayaran::where('id_metodePembayaran', $id)->firstOrFail();

            $metodePembayaran->update([
                'nama' => $request->nama,
                'status' => $request->status,
                'keterangan' => $request->keterangan,
                'tanggal_update' => now(),
            ]);

            DB::commit();

            return redirect()->route('metode-pembayaran.index')
                ->with('success', 'Metode pembayaran berhasil diperbarui!');
        } catch (Exception $e) {
            DB::rollBack();

            return redirect()->back()
                ->with('error', 'Terjadi kesalahan saat memperbarui data.')
                ->withInput();
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $metodePembayaran = MetodePembayaran::where('id_metodePembayaran', $id)->firstOrFail();

            $metodePembayaran->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Metode pembayaran berhasil dihapus!',
            ]);
        } catch (Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menghapus data.',
            ], 500);
        }
    }
}
