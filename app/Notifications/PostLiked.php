<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use App\Models\User;
use App\Models\TimelinePost;

class PostLiked extends Notification
{
    use Queueable;

    public $liker;
    public $post;

    /**
     * Create a new notification instance.
     */
    public function __construct(User $liker, TimelinePost $post)
    {
        $this->liker = $liker;
        $this->post = $post;
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
            'type' => 'like',
            'user_id' => $this->liker->id,
            'user_name' => $this->liker->name,
            'user_avatar' => $this->liker->foto_profil,
            'post_id' => $this->post->id,
            'body' => 'menyukai unggahan Anda.',
        ];
    }
}
