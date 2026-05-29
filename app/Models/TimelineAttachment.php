<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class TimelineAttachment extends Model
{
    protected $fillable = [
        'path',
        'type',
        'original_name',
        'size',
        'sort_order',
    ];

    public function attachable(): MorphTo
    {
        return $this->morphTo();
    }
}