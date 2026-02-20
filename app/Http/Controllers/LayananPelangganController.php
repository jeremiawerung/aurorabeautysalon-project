<?php

namespace App\Http\Controllers;

use App\Models\KategoriLayanan;
use App\Models\Layanan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LayananPelangganController extends Controller
{
    public function search(Request $request)
    {
        try {
            $keyword = $request->input('q', '');

            if (strlen($keyword) < 2) {
                return response()->json([
                    'success' => false,
                    'message' => 'Keyword minimal 2 karakter',
                    'data' => [],
                ]);
            }

            $layanan = Layanan::with('kategoriLayanan')
                ->where('status_layanan', 'aktif')
                ->where(function ($query) use ($keyword) {
                    $query->where('nama_layanan', 'like', '%'.$keyword.'%')
                        ->orWhere('deskripsi', 'like', '%'.$keyword.'%');
                })
                ->limit(8) // Batasi hasil pencarian
                ->get()
                ->map(function ($item) {
                    return [
                        'id_layanan' => $item->id_layanan,
                        'nama_layanan' => $item->nama_layanan,
                        'deskripsi' => $item->deskripsi,
                        'harga' => $item->harga,
                        'durasi' => $item->durasi,
                        'image_url' => $item->gambar
                            ? asset('storage/'.$item->gambar)
                            : asset('img/favicon.svg'),
                        'kategori_layanan' => $item->kategoriLayanan ? [
                            'id' => $item->kategoriLayanan->id_kategoriLayanan,
                            'nama' => $item->kategoriLayanan->nama,
                        ] : null,
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $layanan,
            ]);

        } catch (\Exception $e) {
            Log::error('Error searching layanan: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mencari layanan',
            ], 500);
        }
    }

    public function index(Request $request)
    {
        try {
            $search = $request->input('search', '');
            $kategori = $request->input('kategori', 'all');
            $highlight = $request->input('highlight', null);

            $query = Layanan::with('kategoriLayanan')
                ->where('status_layanan', 'aktif');

            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('nama_layanan', 'like', '%'.$search.'%')
                        ->orWhere('deskripsi', 'like', '%'.$search.'%');
                });
            }

            if ($kategori !== 'all') {
                $query->where('id_kategoriLayanan', $kategori);
            }

            $services = $query->orderBy('nama_layanan', 'asc')->get();

            $categories = KategoriLayanan::withCount(['layanan' => function ($q) {
                $q->where('status_layanan', 'aktif');
            }])->get();

            return view('pelanggan.layanan', compact(
                'services',
                'categories',
                'search',
                'kategori',
                'highlight'
            ));

        } catch (\Exception $e) {
            Log::error('Error loading layanan page: '.$e->getMessage());

            return back()->with('error', 'Terjadi kesalahan saat memuat halaman layanan');
        }
    }
}
