<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ContactController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('admin.contact');
    }

    /**
     * Get contacts data via AJAX
     */
    public function ajax()
    {
        try {
            $contacts = Contact::orderBy('created_at', 'desc')->get();

            return response()->json([
                'success' => true,
                'data' => $contacts,
            ]);
        } catch (\Throwable $e) {
            Log::error('Contact ajax error: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat data',
            ], 500);
        }
    }

    /**
     * Get single contact detail via AJAX
     */
    public function show($id)
    {
        try {
            $contact = Contact::findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $contact,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan',
            ], 404);
        }
    }

    /**
     * Send contact form
     */
    public function send(Request $request)
    {
        // 1. Validasi input
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:150'],
            'phone' => ['required', 'string', 'max:50'],
            'services' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:2000'],
        ]);

        try {
            // Gunakan database transaction
            DB::beginTransaction();

            // 2. Simpan ke database
            $contact = Contact::create($data);

            // 3. Ambil email admin dari config/mail (yang sumbernya dari .env)
            $adminEmail = config('mail.from.address');
            $adminName = config('mail.from.name', 'Admin');

            if (! $adminEmail) {
                throw new \Exception('Email admin (mail.from.address) belum diset.');
            }

            // 4. Kirim email
            Mail::send('emails.contact-notification', ['data' => $data], function ($message) use ($data, $adminEmail, $adminName) {
                $message->to($adminEmail, $adminName)
                    ->subject('Pesan Baru dari Form Kontak')
                    // from pakai email sistem, reply-to ke email user
                    ->replyTo($data['email'], $data['name']);
            });

            // Commit transaction jika semua berhasil
            DB::commit();

            // 5. Redirect balik dengan pesan sukses
            return back()->with('success', __('Terima kasih, pesan Anda sudah terkirim. Kami akan menghubungi Anda secepatnya.'));

        } catch (\Throwable $e) {
            // Rollback jika ada error
            DB::rollBack();

            Log::error('Contact form send error: '.$e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'payload' => $data,
            ]);

            return back()
                ->withInput()
                ->withErrors(['general' => __('Terjadi kesalahan saat mengirim pesan. Silakan coba lagi nanti.')]);
        }
    }
}
