<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
}
