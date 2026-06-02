<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use App\Models\User;
use App\Models\PersonalBook;

class ReturnRequested extends Notification
{
    use Queueable;

    public $borrower;
    public $book;

    /**
     * Create a new notification instance.
     */
    public function __construct(User $borrower, PersonalBook $book)
    {
        $this->borrower = $borrower;
        $this->book = $book;
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
            'type' => 'return',
            'user_id' => $this->borrower->id,
            'user_name' => $this->borrower->name,
            'user_avatar' => $this->borrower->foto_profil,
            'book_id' => $this->book->id,
            'body' => 'mengajukan pengembalian buku "' . $this->book->judul . '".',
        ];
    }
}
