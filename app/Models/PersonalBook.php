<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PersonalBook extends Model
{
    protected $fillable = [
        'user_id', 'judul', 'penulis', 'isbn', 'tahun_terbit', 'kategori', 'cover_url', 'jumlah_halaman', 'is_available', 'status', 'reading_status'
    ];

    protected function casts(): array {
        return [
            'is_available' => 'boolean',
            'tahun_terbit' => 'integer',
            'jumlah_halaman' => 'integer',
        ];
    }

    protected static function booted(): void {
        $syncStatus = function (PersonalBook $pb) {
            // Find matching FeaturedBooks and sync their status
            FeaturedBook::query()
                ->where(function ($q) use ($pb) {
                    if (!empty($pb->isbn)) {
                        $q->where('isbn', $pb->isbn)->orWhere(function ($q2) use ($pb) {
                            $q2->where('judul', $pb->judul)
                               ->where('penulis', $pb->penulis);
                        });
                    } else {
                        $q->where('judul', $pb->judul)
                          ->where('penulis', $pb->penulis);
                    }
                })
                ->get()
                ->each->syncStatus();
        };

        static::created($syncStatus);
        static::updated($syncStatus);
        static::deleted($syncStatus);
    }

    public function user(): BelongsTo {
        return $this->belongsTo(User::class);
    }
}
