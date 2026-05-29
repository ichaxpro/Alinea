<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class TimelineComment extends Model
{
    protected $fillable = [
        'id_post',
        'id_user',
        'isi_komentar',
        'media',
        'media_type',
        'media_original_name',
        'media_size',
    ];

    public function post(): BelongsTo
    {
        return $this->belongsTo(TimelinePost::class, 'id_post');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_user');
    }

    public function attachments(): MorphMany
    {
        return $this->morphMany(TimelineAttachment::class, 'attachable')->orderBy('sort_order')->orderBy('id');
    }
}