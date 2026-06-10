<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ContentHidden extends Notification
{
    use Queueable;

    protected $message;
    protected $type;

    /**
     * Create a new notification instance.
     */
    public function __construct($message = 'Salah satu konten Anda telah disembunyikan oleh admin karena melanggar panduan komunitas.', $type = 'content_hidden')
    {
        $this->message = $message;
        $this->type = $type;
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
            'type' => $this->type,
            'message' => $this->message,
        ];
    }
}
