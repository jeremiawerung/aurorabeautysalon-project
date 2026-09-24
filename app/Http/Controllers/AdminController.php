<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Auth\Events\Registered;


class AdminController extends Controller
{
    public function index()
    {
        $admins = Admin::all();

        return view('admin.index', compact('admins'));
    }

    public function ajax()
    {
        try {
            $admins = Admin::with('user')
                ->select('id_admin', 'nama', 'status_admin', 'user_id')
                ->orderBy('id_admin', 'asc')
                ->get();

            // Transform data to include email from User model
            $data = $admins->map(function ($admin) {
                return [
                    'id_admin' => $admin->id_admin,
                    'nama' => $admin->nama,
                    'email' => $admin->user ? $admin->user->email : '-',
                    'status_admin' => $admin->status_admin,
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $data,
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching admin data: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat data admin',
            ], 500);
        }
    }

    public function showRegistrationForm()
    {
        return view('auth.admin-register');
    }

    public function store(Request $request)
    {
        Log::info('Data Registrasi Admin:', $request->all());

        $request->validate([
            'nama' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        // 1. Create User (SSOT)
        $user = \App\Models\User::create([
             'name' => $request->nama,
             'email' => $request->email,
             'password' => Hash::make($request->password),
             'role' => 'admin',
        ]);

        event(new Registered($user));


        // 2. Create Admin linked to User
        $admin = new Admin;
        $admin->user_id = $user->id; // Foreign Key
        $admin->nama = $request->nama;
        // email & password removed from admin table
        $admin->status_admin = 'aktif';
        $admin->save();

        return redirect()->route('admin.index')
            ->with('success', 'Admin baru berhasil ditambahkan');
    }

    public function edit($id)
    {
        $admin = Admin::findOrFail($id);

        return view('admin.edit', compact('admin'));
    }

    public function update(Request $request, $id)
    {
        $admin = Admin::findOrFail($id);

        $request->validate([
            'nama' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,'.$admin->user_id,
            'password' => 'nullable|string|min:8|confirmed',
            'status_admin' => 'nullable|string|in:aktif,non-aktif',
        ]);

        $admin = Admin::findOrFail($id);
        $user = \App\Models\User::find($admin->user_id);

        $admin->nama = $request->nama;
        // $admin->email = $request->email; // Removed
        
        // Update User Data (SSOT)
        if ($user) {
             $user->name = $request->nama;
             $user->email = $request->email;
             if ($request->filled('password')) {
                 $user->password = Hash::make($request->password);
             }
             $user->save();
        }

        $admin->status_admin = $request->status_admin ?? 'aktif';
        $admin->save();

        return redirect()->route('admin.index')
            ->with('success', 'Data Admin berhasil diupdate');
    }

    public function updatePassword(Request $request, $id)
    {
        $request->validate([
            'password' => 'required|string|min:8|confirmed',
        ]);

        $admin = Admin::findOrFail($id);

        $admin->password = Hash::make($request->password);
        $admin->save();

        return redirect()->route('admin.index')
            ->with('success', 'Password berhasil diubah');
    }

    public function destroy($id)
    {
        try {
            $admin = Admin::findOrFail($id);

            if (Auth::guard('admin')->id() == $id) {
                return redirect()->route('admin.index')
                    ->with('error', 'Tidak dapat menghapus akun yang sedang aktif');
            }

            $admin->delete();

            return redirect()->route('admin.index')
                ->with('success', 'Admin berhasil dihapus');

        } catch (\Exception $e) {
            Log::error('Error deleting admin: '.$e->getMessage());

            return redirect()->route('admin.index')
                ->with('error', 'Gagal menghapus admin');
        }
    }

    public function logout()
    {
        Auth::guard('admin')->logout();

        return redirect('/admin/login')
            ->with('success', 'Berhasil logout');
    }
}
