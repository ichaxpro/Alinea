<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\FeaturedBook;

use Illuminate\Database\Eloquent\SoftDeletes;

class BookReview extends Model
{
    use SoftDeletes;
    protected $fillable = ['book_identifier', 'book_identifier_type', 'user_id', 'rating', 'ulasan', 'helpful'];
    protected $casts = ['rating' => 'integer', 'helpful' => 'integer'];
    protected $attributes = ['helpful' => 0];
    public function user(): BelongsTo {
        return $this->belongsTo(User::class);
    }

    public function getBookTitleAttribute(): string {
        if ($this->book_identifier_type === 'db') {
            $book = FeaturedBook::find((int) $this->book_identifier);
            return $book?->judul ?? $this->book_identifier;
        }
        return $this->book_identifier . ' (Google Books)';
    }
}
