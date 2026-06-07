<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;

#[Fillable(['name', 'username', 'email', 'password', 'role', 'kota', 'no_telp', 'preferred_genres', 'foto_profil', 'deskripsi', 'sp_count', 'is_banned'])]
#[Hidden(['password', 'remember_token', 'deskripsi'])]
class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

    public const ROLE_ADMIN = 'admin';
    public const ROLE_USER = 'user';

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'preferred_genres' => 'array',
        ];
    }

    public function isAdmin(): bool {
        return $this->role === self::ROLE_ADMIN;
    }

    public function personalBooks(): HasMany {
        return $this->hasMany(PersonalBook::class);
    }

    public function timelinePosts(): HasMany
    {
        return $this->hasMany(TimelinePost::class, 'id_user');
    }

    public function bookmarkedPosts(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(TimelinePost::class, 'post_bookmarks', 'id_user', 'id_post')->withTimestamps();
    }

    public function timelineComments(): HasMany
    {
        return $this->hasMany(TimelineComment::class, 'id_user');
    }

    protected $appends = ['avatar_url'];

    public function getAvatarUrlAttribute(): ?string {
        if (! $this->foto_profil) {
            return null;
        }

        try {
            /** @var \Illuminate\Filesystem\FilesystemAdapter $disk */
            $disk = Storage::disk('public');
            return $disk->url($this->foto_profil);
        } catch (\Throwable $e) {
            return asset('storage/' . $this->foto_profil);
        }
    }

    public function bookmarks(): HasMany {
        return $this->hasMany(Bookmark::class);
    }

    public function borrowedBooks()
{
    return $this->hasMany(Transaction::class, 'borrower_id');
}

    public function followers(): HasMany {
        return $this->hasMany(Follow::class, 'following_id');
    }

    public function following(): HasMany {
        return $this->hasMany(Follow::class, 'follower_id');
    }

    public function followersCount(): int {
        return $this->followers()->count();
    }

    public function followingCount(): int {
        return $this->following()->count();
    }

    public function achievements(): BelongsToMany {
        return $this->belongsToMany(Achievement::class, 'user_achievement')
            ->withPivot('earned_at')
            ->withTimestamps();
    }

    public function readingBooks(): HasMany {
        return $this->hasMany(PersonalBook::class)->whereNotNull('reading_status');
    }

    public function blockedUsers(): BelongsToMany {
        return $this->belongsToMany(User::class, 'blocks', 'user_id', 'blocked_user_id')->withTimestamps();
    }

    public function blockedBy(): BelongsToMany {
        return $this->belongsToMany(User::class, 'blocks', 'blocked_user_id', 'user_id')->withTimestamps();
    }

    public function scopeExcludeBlocked($query)
    {
        if (auth()->check()) {
            $authId = auth()->id();
            $blockedByAuth = \Illuminate\Support\Facades\DB::table('blocks')->where('user_id', $authId)->pluck('blocked_user_id');
            $blockedAuth = \Illuminate\Support\Facades\DB::table('blocks')->where('blocked_user_id', $authId)->pluck('user_id');
            $excludedIds = $blockedByAuth->merge($blockedAuth)->unique();

            if ($excludedIds->isNotEmpty()) {
                $query->whereNotIn('id', $excludedIds);
            }
        }
        return $query;
    }

    public function canAccessPanel(Panel $panel): bool {
        return $this->role === self::ROLE_ADMIN;
    }
}
