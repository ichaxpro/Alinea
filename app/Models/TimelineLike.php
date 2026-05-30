<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TimelineLike extends Model
{
    protected $fillable = [
        'id_post',
        'id_user',
    ];

    public function post(): BelongsTo
    {
        return $this->belongsTo(TimelinePost::class, 'id_post');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_user');
    }
}
