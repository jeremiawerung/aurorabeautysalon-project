<?php

namespace App\Traits;

use App\Models\User;
use App\Notifications\AdminNotification;
use Illuminate\Support\Facades\Log;

trait AdminNotifiable
{
    /**
     * Helper to send notifications to all admin users.
     *
     * @param array $data ['title', 'message', 'type', 'link', 'icon']
     */
    protected function notifyAdmins($data)
    {
        try {
            // Ambil semua user dengan role 'admin'
            $admins = User::where('role', 'admin')->get();

            foreach ($admins as $admin) {
                // Pastikan admin notifiable
                if (method_exists($admin, 'notify')) {
                    $admin->notify(new AdminNotification($data));
                }
            }
        } catch (\Exception $e) {
            Log::error('Gagal mengirim notifikasi admin: ' . $e->getMessage());
        }
    }
}
