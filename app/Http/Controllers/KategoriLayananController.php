<?php

namespace App\Http\Controllers;

use App\Models\KategoriLayanan;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class KategoriLayananController extends Controller
{
    public function index()
    {
        $kategoriLayanan = KategoriLayanan::withCount('layanan')->get();

        return view('admin.kategori_layanan.index', compact('kategoriLayanan'));
    }

    public function ajax()
    {
        try {
            $kategoriLayanan = KategoriLayanan::withCount('layanan')->get()
                ->map(function ($item) {
                    $item->image_url = $item->gambar
                        ? asset('storage/'.$item->gambar)
                        : asset('img/favicon.svg'); // Fallback

                    return $item;
                });

            return response()->json([
                'success' => true,
                'data' => $kategoriLayanan,
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error saat mengambil Kategori Layanan: '.$e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil data.',
            ], 500);
        }
    }

    public function ajaxpelanggan()
    {
        try {
            $kategoriLayanan = KategoriLayanan::get()
                ->map(function ($item) {
                    $item->image_url = $item->gambar
                        ? asset('storage/'.$item->gambar)
                        : asset('img/favicon.svg'); // Fallback

                    return $item;
                });

            return response()->json([
                'success' => true,
                'data' => $kategoriLayanan,
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error saat mengambil Kategori Layanan: '.$e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil data.',
            ], 500);
        }
    }

    public function create()
    {
        return view('admin.kategori_layanan.create');
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'nama' => [
                'required',
                'string',
                'max:255',
                Rule::unique('kategori_layanan', 'nama'), // unique GLOBAL
            ],
            'status' => 'required|string|in:aktif,non-aktif',
            'keterangan' => 'nullable|string',
            'gambar' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $gambarPath = null;
        $directoryPath = 'kategori-layanan';

        try {
            if ($request->hasFile('gambar')) {
                if (! Storage::disk('public')->exists($directoryPath)) {
                    Storage::disk('public')->makeDirectory($directoryPath);
                }
                $gambarPath = $request->file('gambar')->store($directoryPath, 'public');
                $validatedData['gambar'] = $gambarPath;
            }

            // Tidak lagi menyetel id_admin
            $validatedData['tanggal_update'] = now();

            KategoriLayanan::create($validatedData);

            session()->flash('success', 'Kategori layanan berhasil ditambahkan!');

            return redirect()->route('kategori-layanan.index');

        } catch (\Exception $e) {
            if ($gambarPath && Storage::disk('public')->exists($gambarPath)) {
                Storage::disk('public')->delete($gambarPath);
            }

            Log::error('Error KategoriLayananController@store: '.$e->getMessage());
            session()->flash('error', 'Terjadi kesalahan saat menyimpan data. Silakan coba lagi.');

            return redirect()->back()->withInput();
        }
    }

    public function edit($id)
    {
        try {
            $kategoriLayanan = KategoriLayanan::findOrFail($id);

            return view('admin.kategori_layanan.edit', compact('kategoriLayanan'));

        } catch (ModelNotFoundException $e) {
            session()->flash('error', 'Data kategori tidak ditemukan.');

            return redirect()->route('kategori-layanan.index');
        }
    }

    public function show($id)
    {
        return $this->edit($id);
    }

    public function update(Request $request, $id)
    {
        $validatedData = $request->validate([
            'nama' => [
                'required',
                'string',
                'max:255',
                Rule::unique('kategori_layanan', 'nama')
                    ->ignore($id, 'id_kategoriLayanan'),
            ],
            'status' => 'required|string|in:aktif,non-aktif',
            'keterangan' => 'nullable|string',
            'gambar' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $newImagePath = null;
        $oldImagePath = null;
        $directoryPath = 'kategori-layanan';

        try {
            $kategoriLayanan = KategoriLayanan::findOrFail($id);

            $oldImagePath = $kategoriLayanan->gambar;

            if ($request->hasFile('gambar')) {
                if (! Storage::disk('public')->exists($directoryPath)) {
                    Storage::disk('public')->makeDirectory($directoryPath);
                }

                $newImagePath = $request->file('gambar')->store($directoryPath, 'public');
                $validatedData['gambar'] = $newImagePath;
            }

            $validatedData['tanggal_update'] = now();
            $kategoriLayanan->update($validatedData);

            if ($newImagePath && $oldImagePath && Storage::disk('public')->exists($oldImagePath)) {
                Storage::disk('public')->delete($oldImagePath);
            }

            session()->flash('success', 'Kategori layanan berhasil diperbarui!');

            return redirect()->route('kategori-layanan.index');

        } catch (ModelNotFoundException $e) {
            session()->flash('error', 'Data kategori tidak ditemukan.');

            return redirect()->route('kategori-layanan.index');

        } catch (\Exception $e) {
            if ($newImagePath && Storage::disk('public')->exists($newImagePath)) {
                Storage::disk('public')->delete($newImagePath);
            }

            Log::error('Error KategoriLayananController@update: '.$e->getMessage());
            session()->flash('error', 'Terjadi kesalahan saat memperbarui data. Silakan coba lagi.');

            return redirect()->back()->withInput();
        }
    }

    public function destroy($id)
    {
        try {
            $kategoriLayanan = KategoriLayanan::findOrFail($id);

            $imagePath = $kategoriLayanan->gambar;

            $kategoriLayanan->delete();

            if ($imagePath && Storage::disk('public')->exists($imagePath)) {
                Storage::disk('public')->delete($imagePath);
            }

            session()->flash('success', 'Kategori layanan berhasil dihapus!');

            return redirect()->route('kategori-layanan.index');

        } catch (ModelNotFoundException $e) {
            session()->flash('error', 'Data kategori tidak ditemukan.');

            return redirect()->route('kategori-layanan.index');

        } catch (\Exception $e) {
            Log::error('Error KategoriLayananController@destroy: '.$e->getMessage());
            session()->flash('error', 'Gagal menghapus data. Data mungkin masih digunakan oleh layanan lain.');

            return redirect()->route('kategori-layanan.index');
        }
    }
}
