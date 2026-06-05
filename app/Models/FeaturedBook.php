<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\BookReview;

class FeaturedBook extends Model
{
    protected $fillable = ['judul', 'penulis', 'penerbit', 'tahun', 'sinopsis', 'genres', 'cover_url', 'gradient_from', 'gradient_to', 'isbn', 'jumlah_halaman', 'bahasa', 'kategori', 'status', 'rating_avg', 'rating_count',];

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

    public function syncStatus(): void {
        $hasOwners = PersonalBook::where('is_available', true)
            ->where(function ($q) {
                if (!empty($this->isbn)) {
                    $q->where('isbn', $this->isbn)->orWhere(function ($q2) {
                        $q2->where('judul', $this->judul)
                           ->where('penulis', $this->penulis);
                    });
                } else {
                    $q->where('judul', $this->judul)
                      ->where('penulis', $this->penulis);
                }
            })
            ->exists();

        $newStatus = $hasOwners ? 'tersedia' : 'tidak_tersedia';
        if ($this->status !== $newStatus) {
            $this->update(['status' => $newStatus]);
        }
    }

    public static function syncAllStatuses(): void {
        static::all()->each->syncStatus();
    }
}
