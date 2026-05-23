<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PersonalBook extends Model
{
    protected $fillable = [
        'user_id', 'judul', 'penulis', 'isbn', 'tahun_terbit', 'kategori', 'cover_url', 'jumlah_halaman', 'is_available', 'status'
    ];

    protected function casts(): array {
        return [
            'is_available' => 'boolean',
            'tahun_terbit' => 'integer',
            'jumlah_halaman' => 'integer',
        ];
    }

    public function user(): BelongsTo {
        return $this->belongsTo(User::class);
    }
}
