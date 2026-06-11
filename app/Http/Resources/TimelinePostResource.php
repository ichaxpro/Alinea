<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TimelinePostResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $currentUser = auth()->user();
        
        $attachments = $this->relationLoaded('attachments') ? $this->attachments : $this->attachments()->get();
        $payloadAttachments = AttachmentResource::collection($attachments)->resolve();
        $firstAttachment = $payloadAttachments[0] ?? null;
        
        $likesCount = $this->likes_count ?? ($this->relationLoaded('likes') ? $this->likes->count() : 0);
        $liked = false;
        if ($currentUser) {
            if ($this->relationLoaded('likes')) {
                $liked = $this->likes->contains('id_user', $currentUser->id);
            } else {
                $liked = \App\Models\TimelineLike::where('id_post', $this->id)->where('id_user', $currentUser->id)->exists();
            }
        }
        $likes_label = $likesCount >= 1000 ? round($likesCount/1000, 1) . 'K' : (string) $likesCount;
        
        $commentsCount = $this->comments_count ?? 0;
        $comments_label = $commentsCount >= 1000 ? round($commentsCount/1000, 1) . 'K' : (string) $commentsCount;

        $bookmarked = false;
        if ($currentUser) {
            $bookmarked = \App\Models\PostBookmark::where('id_post', $this->id)->where('id_user', $currentUser->id)->exists();
        }

        $author = clone ($this->relationLoaded('author') ? $this->author : ($this->author()->first() ?? new \App\Models\User));

        $is_following = false;
        if ($currentUser && $currentUser->id !== $this->id_user) {
            $is_following = \App\Models\Follow::where('follower_id', $currentUser->id)->where('following_id', $this->id_user)->exists();
        }

        return [
            'id' => $this->id,
            'user_id' => $this->id_user,
            'name' => $author->name ?? 'Pengguna',
            'username' => $author->username ?? null,
            'profile_url' => $author->username ? route('profile.by_username', ['username' => ltrim($author->username, '@')]) : '#',
            'handle' => $author->username ? '@' . ltrim($author->username, '@') : '@pengguna',
            'location' => $author->kota ?: 'Online',
            'time' => $this->created_at ? \Carbon\Carbon::parse($this->created_at)->locale('id')->diffForHumans() : 'Baru saja',
            'absolute_time' => $this->created_at ? \Carbon\Carbon::parse($this->created_at)->timezone('Asia/Jakarta')->locale('id')->translatedFormat('d M Y, H:i') : 'Baru saja',
            'book' => $this->judul_buku_dibahas,
            'body' => $this->pesan,
            'comments' => $comments_label,
            'likes_base' => $likesCount,
            'likes_label' => $likes_label,
            'liked' => $liked,
            'bookmarked' => $bookmarked,
            'is_following' => $is_following,
            'avatar_url' => !empty($author->foto_profil) ? asset('storage/' . $author->foto_profil) : ($author->avatar_url ?? null),
            'avatar_from' => '#FFDDAF',
            'avatar_to' => '#C7E7FF',
            'tag' => $this->tag ?: 'Post',
            'klub' => $this->relationLoaded('club') && $this->club ? $this->club->nama_klub : null,
            'media' => $firstAttachment['path'] ?? $this->media ?? null,
            'media_url' => $firstAttachment['url'] ?? ($this->media ? asset('storage/' . $this->media) : null),
            'media_type' => $firstAttachment['type'] ?? $this->media_type ?? null,
            'media_original_name' => $firstAttachment['original_name'] ?? $this->media_original_name ?? null,
            'media_size' => $firstAttachment['size'] ?? $this->media_size ?? null,
            'attachments' => $payloadAttachments,
        ];
    }
}
