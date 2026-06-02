<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Achievement extends Model
{
    protected $fillable = ['key', 'title', 'description', 'icon', 'criteria_type', 'criteria_value'];

    public function users(): BelongsToMany {
        return $this->belongsToMany(User::class, 'user_achievement')
            ->withPivot('earned_at')
            ->withTimestamps();
    }
}
