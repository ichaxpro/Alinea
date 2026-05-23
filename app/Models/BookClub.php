<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class BookClub extends Model
{
    protected $table = 'klub';

    protected $fillable = [
        'nama_klub',
        'kategori',
        'deskripsi',
        // 'jadwal',
        'foto_klub',
        'id_owner',
        'gradient_from',
        'gradient_to',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_owner');
    }
}
