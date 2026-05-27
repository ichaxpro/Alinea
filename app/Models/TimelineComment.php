<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TimelineComment extends Model
{
    protected $fillable = [
        'id_post',
        'id_user',
        'isi_komentar',
    ];

    public function post(): BelongsTo
    {
        return $this->belongsTo(TimelinePost::class, 'id_post');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_user');
    }
}