<?php

namespace App\Http\Controllers;

use App\Models\KategoriLayanan;
use App\Models\Layanan;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class LayananController extends Controller
{
    public function index()
    {
        return view('admin.layanan.index');
    }

    public function ajax()
    {
        try {
            $layanan = Layanan::with('kategoriLayanan')
                ->get()
                ->map(function ($item) {
                    $harga = (int) floatval($item->harga);
                    $item->formatted_harga = number_format($harga, 0, '', '.');

                    $item->image_url = $item->gambar
                        ? asset('storage/'.$item->gambar)
                        : asset('img/favicon.svg');

                    return $item;
                });

            return response()->json([
                'success' => true,
                'data' => $layanan,
            ]);
        } catch (Exception $e) {
            Log::error('Error fetching layanan AJAX: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat data layanan.',
            ], 500);
        }
    }

    public function ajaxpelanggan()
    {
        try {
            $layanan = Layanan::with('kategoriLayanan')
                ->where('status_layanan', 'aktif')
                ->get()
                ->map(function ($item) {
                    $harga = (int) floatval($item->harga);
                    $item->formatted_harga = number_format($harga, 0, '', '.');

                    $item->image_url = $item->gambar
                        ? asset('storage/'.$item->gambar)
                        : asset('img/favicon.svg');

                    return $item;
                });

            return response()->json([
                'success' => true,
                'data' => $layanan,
            ]);
        } catch (Exception $e) {
            Log::error('Error fetching layanan AJAX pelanggan: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat data layanan.',
            ], 500);
        }
    }

    public function import(Request $request)
    {
        try {
            $request->validate([
                'file' => 'required|file|mimes:xlsx,xls|max:2048',
            ]);

            $file = $request->file('file');
            $data = Excel::toArray([], $file);

            if (empty($data) || ! isset($data[0])) {
                return back()->with('error', '❌ File Excel kosong atau tidak memiliki sheet yang valid.');
            }

            $rows = $data[0];
            $isHeader = true;

            $requiredColumns = [
                'nama_layanan', 'id_kategoriLayanan', 'harga', 'deskripsi', 'durasi', 'status_layanan',
            ];

            $inserted = 0;
            $skipped = 0;

            DB::beginTransaction();

            foreach ($rows as $rowIndex => $row) {
                if ($isHeader) {
                    $isHeader = false;

                    continue;
                }

                if (count($row) < count($requiredColumns)) {
                    $skipped++;

                    continue;
                }

                $record = [
                    'nama_layanan' => trim($row[0] ?? ''),
                    'id_kategoriLayanan' => trim($row[1] ?? ''),
                    'harga' => trim($row[2] ?? ''),
                    'deskripsi' => trim($row[3] ?? ''),
                    'durasi' => trim($row[4] ?? ''),
                    'status_layanan' => trim($row[5] ?? ''),
                ];

                if (empty($record['nama_layanan']) || empty($record['harga']) || empty($record['durasi'])) {
                    $skipped++;

                    continue;
                }

                if (! is_numeric($record['harga']) || ! is_numeric($record['durasi'])) {
                    $skipped++;

                    continue;
                }

                Layanan::create($record);
                $inserted++;
            }

            DB::commit();

            return back()->with('success', "✅ Import selesai! Berhasil: {$inserted}, Dilewati: {$skipped} baris tidak valid.");
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Import Layanan Error: '.$e->getMessage(), ['trace' => $e->getTraceAsString()]);

            return back()->with('error', '❌ Gagal import: '.$e->getMessage());
        }
    }

    public function create()
    {
        $kategoriLayanans = KategoriLayanan::where('status', 'aktif')->get();

        return view('admin.layanan.create', compact('kategoriLayanans'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        if ($user->role !== 'admin') {
            abort(403, 'Hanya admin yang dapat menambahkan layanan.');
        }

        $validatedData = $request->validate([
            'nama_layanan' => 'required|string|max:60',
            'harga' => 'required|numeric|min:0',
            'deskripsi' => 'nullable|string',
            'durasi' => 'required|integer|min:1',
            'status_layanan' => 'required|string|in:aktif,non-aktif',
            'id_kategoriLayanan' => 'required|integer|exists:kategori_layanan,id_kategoriLayanan',
            'gambar' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $gambarPath = null;
        $directoryPath = 'layanan';

        try {
            if ($request->hasFile('gambar')) {
                if (! Storage::disk('public')->exists($directoryPath)) {
                    Storage::disk('public')->makeDirectory($directoryPath);
                }
                $gambarPath = $request->file('gambar')->store($directoryPath, 'public');
                $validatedData['gambar'] = $gambarPath;
            }

            Layanan::create($validatedData);

            return redirect()->route('layanan.index')
                ->with('success', 'Layanan berhasil ditambahkan!');
        } catch (Exception $e) {
            if ($gambarPath && Storage::disk('public')->exists($gambarPath)) {
                Storage::disk('public')->delete($gambarPath);
            }

            Log::error('Error LayananController@store: '.$e->getMessage());

            return redirect()->back()
                ->with('error', 'Terjadi kesalahan saat menyimpan data. Silakan coba lagi.')
                ->withInput();
        }
    }

    public function edit($id)
    {
        try {
            $layanan = Layanan::findOrFail($id);

            $kategoriLayanans = KategoriLayanan::where('status', 'aktif')->get();

            return view('admin.layanan.edit', compact('layanan', 'kategoriLayanans'));
        } catch (ModelNotFoundException $e) {
            return redirect()->route('layanan.index')
                ->with('error', 'Data layanan tidak ditemukan.');
        }
    }

    public function update(Request $request, $id)
    {
        $user = Auth::user();
        if ($user->role !== 'admin') {
            abort(403, 'Hanya admin yang dapat mengubah layanan.');
        }

        $validatedData = $request->validate([
            'nama_layanan' => 'required|string|max:60',
            'harga' => 'required|numeric|min:0',
            'deskripsi' => 'nullable|string',
            'durasi' => 'required|integer|min:1',
            'status_layanan' => 'required|string|in:aktif,non-aktif',
            'id_kategoriLayanan' => 'required|integer|exists:kategori_layanan,id_kategoriLayanan',
            'gambar' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $newImagePath = null;
        $oldImagePath = null;
        $directoryPath = 'layanan';

        try {
            $layanan = Layanan::findOrFail($id);

            $oldImagePath = $layanan->gambar;

            if ($request->hasFile('gambar')) {
                if (! Storage::disk('public')->exists($directoryPath)) {
                    Storage::disk('public')->makeDirectory($directoryPath);
                }
                $newImagePath = $request->file('gambar')->store($directoryPath, 'public');
                $validatedData['gambar'] = $newImagePath;
            }

            // Tidak menetapkan id_admin
            $layanan->update($validatedData);

            if ($newImagePath && $oldImagePath && Storage::disk('public')->exists($oldImagePath)) {
                Storage::disk('public')->delete($oldImagePath);
            }

            return redirect()->route('layanan.index')
                ->with('success', 'Layanan berhasil diupdate!');
        } catch (ModelNotFoundException $e) {
            return redirect()->route('layanan.index')
                ->with('error', 'Data layanan tidak ditemukan.');
        } catch (Exception $e) {
            if ($newImagePath && Storage::disk('public')->exists($newImagePath)) {
                Storage::disk('public')->delete($newImagePath);
            }

            Log::error('Error LayananController@update: '.$e->getMessage());

            return redirect()->back()
                ->with('error', 'Terjadi kesalahan saat mengupdate data. Silakan coba lagi.')
                ->withInput();
        }
    }

    public function destroy($id)
    {
        try {
            $layanan = Layanan::findOrFail($id);

            $imagePath = $layanan->gambar;

            $layanan->delete();

            if ($imagePath && Storage::disk('public')->exists($imagePath)) {
                Storage::disk('public')->delete($imagePath);
            }

            return redirect()->route('layanan.index')
                ->with('success', 'Layanan berhasil dihapus!');
        } catch (ModelNotFoundException $e) {
            return redirect()->route('layanan.index')
                ->with('error', 'Data layanan tidak ditemukan.');
        } catch (Exception $e) {
            Log::error('Error LayananController@destroy: '.$e->getMessage());

            return redirect()->route('layanan.index')
                ->with('error', 'Gagal menghapus data. Layanan ini mungkin masih digunakan.');
        }
    }
}
