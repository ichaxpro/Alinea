<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FeaturedBook extends Model
{
    protected $fillable = ['judul', 'penulis', 'tahun', 'sinopsis', 'genres', 'cover_url', 'gradient_from', 'gradient_to'];

    protected function casts(): array
    {
        return [
            'genres' => 'array',
        ];
    }
}
