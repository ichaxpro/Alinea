<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookReview extends Model
{
    protected $fillable = ['book_identifier', 'book_identifier_type', 'user_id', 'rating', 'ulasan', 'helpful'];
    protected $casts = ['rating' => 'integer', 'helpful' => 'integer'];
    public function user(): BelongsTo {
        return $this->belongsTo(User::class);
    }
}
