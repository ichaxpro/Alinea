<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TimelineCommentLike extends Model
{
    protected $fillable = [
        'id_comment',
        'id_user',
    ];

    public function comment(): BelongsTo
    {
        return $this->belongsTo(TimelineComment::class, 'id_comment');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_user');
    }
}
