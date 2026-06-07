<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class TimelinePost extends Model
{
    use SoftDeletes;
    
    protected static function booted()
    {
        static::addGlobalScope('excludeBlocked', function (\Illuminate\Database\Eloquent\Builder $builder) {
            if (auth()->check()) {
                $authId = auth()->id();
                
                $blockedByAuth = \Illuminate\Support\Facades\DB::table('blocks')
                    ->where('user_id', $authId)
                    ->pluck('blocked_user_id');
                    
                $blockedAuth = \Illuminate\Support\Facades\DB::table('blocks')
                    ->where('blocked_user_id', $authId)
                    ->pluck('user_id');
                    
                $excludedIds = $blockedByAuth->merge($blockedAuth)->unique();
                
                if ($excludedIds->isNotEmpty()) {
                    $builder->whereNotIn('id_user', $excludedIds);
                }
            }
        });
    }

    protected $fillable = [
        'id_user',
        'id_klub',
        'judul_buku_dibahas',
        'pesan',
        'tag',
        'media',
        'media_type',
        'media_original_name',
        'media_size',
    ];

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_user');
    }

    public function club(): BelongsTo
    {
        return $this->belongsTo(BookClub::class, 'id_klub');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(TimelineComment::class, 'id_post');
    }

    public function attachments(): MorphMany
    {
        return $this->morphMany(TimelineAttachment::class, 'attachable')->orderBy('sort_order')->orderBy('id');
    }

    public function likes(): HasMany
    {
        return $this->hasMany(TimelineLike::class, 'id_post');
    }

    public function bookmarkedBy(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(User::class, 'post_bookmarks', 'id_post', 'id_user')->withTimestamps();
    }
}