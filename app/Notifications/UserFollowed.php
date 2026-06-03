<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use App\Models\User;

class UserFollowed extends Notification
{
    use Queueable;

    public $follower;

    /**
     * Create a new notification instance.
     */
    public function __construct(User $follower)
    {
        $this->follower = $follower;
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
            'type' => 'follow',
            'user_id' => $this->follower->id,
            'user_name' => $this->follower->name,
            'user_avatar' => $this->follower->foto_profil,
            'body' => 'mulai mengikuti Anda.',
        ];
    }
}
