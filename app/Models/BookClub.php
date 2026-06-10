<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BookClub extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'klub';

    protected $fillable = [
        'nama_klub',
        'kategori',
        'deskripsi',
        // 'jadwal',
        'foto_klub',
        'id_owner',
        'admin_id',
        'gradient_from',
        'gradient_to',
        'member_count',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_owner');
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }
}
