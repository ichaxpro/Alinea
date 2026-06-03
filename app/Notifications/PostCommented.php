<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use App\Models\User;
use App\Models\TimelinePost;
use App\Models\TimelineComment;

class PostCommented extends Notification
{
    use Queueable;

    public $commenter;
    public $post;
    public $comment;

    /**
     * Create a new notification instance.
     */
    public function __construct(User $commenter, TimelinePost $post, TimelineComment $comment)
    {
        $this->commenter = $commenter;
        $this->post = $post;
        $this->comment = $comment;
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
            'type' => 'comment',
            'user_id' => $this->commenter->id,
            'user_name' => $this->commenter->name,
            'user_avatar' => $this->commenter->foto_profil,
            'post_id' => $this->post->id,
            'comment_id' => $this->comment->id,
            'body' => 'mengomentari unggahan Anda.',
        ];
    }
}
