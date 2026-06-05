<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\BookReview;

class FeaturedBook extends Model
{
    protected $fillable = ['judul', 'penulis', 'tahun', 'sinopsis', 'genres', 'cover_url', 'gradient_from', 'gradient_to', 'isbn', 'jumlah_halaman', 'bahasa', 'kategori', 'status', 'rating_avg', 'rating_count',];

    protected function casts(): array
    {
        return [
            'genres' => 'array',
            'rating_avg' => 'decimal:2',
        ];
    }

    public function syncRatings(): void {
        $stats = BookReview::where('book_identifier', (string) $this->id)
            ->where('book_identifier_type', 'db')
            ->selectRaw('ROUND(AVG(rating), 2) as avg_rating, COUNT(*) as count')
            ->first();
        
        $this->update([
            'rating_avg' => $stats?->avg_rating ?? 0,
            'rating_count' => $stats?->count ?? 0,
        ]);
    }
}
