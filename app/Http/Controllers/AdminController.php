<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;


class AdminController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $admins = \App\Models\Admin::all();  // Ambil semua data dari tabel 'admin'
        return view('admin.index', compact('admins'));  // Kirim data ke view admin.index
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    // Method untuk menampilkan halaman form registrasi admin
    public function showRegistrationForm()
    {
        return view('auth.admin-register');  // Ganti dengan nama tampilan form registrasi admin
    }

    /**
     * Store a newly created resource in storage.
     */
   public function store(Request $request)
{
    // Dump dan hentikan eksekusi untuk melihat seluruh input request

    Log::info('Data Registrasi Admin:', $request->all());

    // Validasi input
    $request->validate([
        'nama' => 'required|string|max:255',
        'email' => 'required|email|unique:admin,email',
        'password' => 'required|string|min:8|confirmed',  // Pastikan password terkonfirmasi
    ]);

    // Dump hasil validasi

    // Mengenkripsi password sebelum disimpan
    $admin = new Admin();
    $admin->nama = $request->nama;
    $admin->email = $request->email;
    $admin->password = Hash::make($request->password);
    $admin->status_admin = 'aktif'; // ✅ Tambahkan ini

    // Dump data sebelum menyimpan ke database

    $admin->save();

    // Redirect ke halaman login admin setelah pendaftaran berhasil
    return redirect()->route('admin.register')->with('success', 'Admin baru berhasil ditambahkan');
}


    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $admin = Admin::findOrFail($id);
        return view('admin.edit', compact('admin'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        // Validasi input
        $request->validate([
            'nama' => 'required|string|max:255',
            'email' => 'required|email|unique:admin,email,' . $id . ',id_admin',  // Perbaiki validasi email
            'password' => 'nullable|string|min:8|confirmed',  // Password opsional
            'status_admin' => 'nullable|string|in:aktif,non-aktif',  // Validasi status_admin hanya bisa 'aktif' atau 'non-aktif'
        ]);

        // Temukan admin berdasarkan ID (id_admin)
        $admin = Admin::findOrFail($id);

        // Update data admin
        $admin->nama = $request->nama;
        $admin->email = $request->email;
        if ($request->password) {
            $admin->password = Hash::make($request->password);  // Mengupdate password jika diisi
        }
        $admin->status_admin = $request->status_admin ?? 'aktif';  // Update status_admin, jika kosong set default 'aktif'
        $admin->save();  // Simpan perubahan

        return redirect()->route('admin.index')->with('success', 'Data Admin berhasil diupdate');
    }

    public function updatePassword(Request $request, $id)
    {
        // Validasi input
        $request->validate([
            'password' => 'required|string|min:8|confirmed',  // Pastikan password terkonfirmasi
        ]);

        // Temukan admin berdasarkan ID
        $admin = Admin::find($id);

        // Mengenkripsi password sebelum disimpan
        $admin->password = Hash::make($request->password); // Mengenkripsi password
        $admin->save();

        // Redirect setelah password berhasil diubah
        return redirect()->route('admin.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $admin = Admin::findOrFail($id);
        $admin->delete();

        return redirect()->route('admin.index')->with('success', 'Admin berhasil dihapus');
    }

    // Fungsi untuk logout
    public function logout()
    {
        Auth::guard('admin')->logout();  // Logout admin
        return redirect('/admin/login'); // Redirect ke halaman login
    }
}
