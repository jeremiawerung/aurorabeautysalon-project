<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AdminNotification extends Notification
{
    use Queueable;

    public $data;

    /**
     * Create a new notification instance.
     *
     * @param array $data ['title', 'message', 'type', 'link', 'icon']
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title'   => $this->data['title'] ?? 'Notifikasi',
            'message' => $this->data['message'] ?? '',
            'type'    => $this->data['type'] ?? 'info', // info, success, warning, danger
            'link'    => $this->data['link'] ?? '#',
            'icon'    => $this->data['icon'] ?? 'fas fa-bell',
        ];
    }
}
