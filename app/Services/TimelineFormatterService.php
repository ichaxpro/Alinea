<?php

namespace App\Services;

use App\Models\TimelineComment;
use App\Models\TimelinePost;
use Illuminate\Support\Carbon;

class TimelineFormatterService
{
    public function attachmentPayload(object $attachment): array
    {
        return [
            'id' => $attachment->id,
            'path' => $attachment->path,
            'url' => asset('storage/' . $attachment->path),
            'type' => $attachment->type,
            'original_name' => $attachment->original_name,
            'size' => $attachment->size,
            'sort_order' => $attachment->sort_order,
        ];
    }

    public function detectMediaType(?string $mime): string
    {
        $mime = $mime ?: '';

        if (str_starts_with($mime, 'image/')) {
            return 'image';
        }

        if (str_starts_with($mime, 'video/')) {
            return 'video';
        }

        return 'file';
    }

    public function timelineCommentPayload(TimelineComment $comment): array
    {
        $author = $comment->author;
        $attachments = $comment->relationLoaded('attachments')
            ? $comment->attachments
            : $comment->attachments()->get();
        $payloadAttachments = collect($attachments)
            ->map(fn ($attachment) => $this->attachmentPayload($attachment))
            ->values()
            ->all();
        $firstAttachment = $payloadAttachments[0] ?? null;

        return [
            'id' => $comment->id,
            'name' => $author?->name ?? 'Pengguna',
            'username' => $author?->username,
            'profile_url' => $author?->username ? route('profile.by_username', ['username' => ltrim($author->username, '@')]) : '#',
            'handle' => $author?->username ? '@' . ltrim($author->username, '@') : '@pengguna',
            'avatar_url' => $author?->avatar_url ?? null,
            'body' => $comment->isi_komentar,
            'media' => $firstAttachment['path'] ?? $comment->media,
            'media_url' => $firstAttachment['url'] ?? ($comment->media ? asset('storage/' . $comment->media) : null),
            'media_type' => $firstAttachment['type'] ?? $comment->media_type,
            'media_original_name' => $firstAttachment['original_name'] ?? $comment->media_original_name,
            'media_size' => $firstAttachment['size'] ?? $comment->media_size,
            'attachments' => $payloadAttachments,
            'time' => $comment->created_at ? Carbon::parse($comment->created_at)->locale('id')->diffForHumans() : 'Baru saja',
            'absolute_time' => $comment->created_at ? Carbon::parse($comment->created_at)->timezone('Asia/Jakarta')->locale('id')->translatedFormat('d M Y, H:i') : 'Baru saja',
        ];
    }

    public function timelinePostPayload(TimelinePost $post, ?\App\Models\User $currentUser = null): array
    {
        $payloadAttachments = [];
        if ($post->relationLoaded('attachments') || method_exists($post, 'attachments')) {
            $attachments = $post->relationLoaded('attachments') ? $post->attachments : $post->attachments()->get();
            $payloadAttachments = collect($attachments)->map(fn($attachment) => $this->attachmentPayload($attachment))->values()->all();
        }

        $firstAttachment = $payloadAttachments[0] ?? null;
        
        $likesCount = $post->likes_count ?? ($post->relationLoaded('likes') ? $post->likes->count() : 0);
        $liked = false;
        if ($currentUser) {
            if ($post->relationLoaded('likes')) {
                $liked = $post->likes->contains('id_user', $currentUser->id);
            } else {
                $liked = \App\Models\TimelineLike::where('id_post', $post->id)->where('id_user', $currentUser->id)->exists();
            }
        }
        
        $likes_label = $likesCount >= 1000 ? round($likesCount/1000, 1) . 'K' : (string) $likesCount;
        
        $commentsCount = $post->comments_count ?? 0;
        $comments_label = $commentsCount >= 1000 ? round($commentsCount/1000, 1) . 'K' : (string) $commentsCount;

        $bookmarked = false;
        if ($currentUser) {
            $bookmarked = \App\Models\PostBookmark::where('id_post', $post->id)->where('id_user', $currentUser->id)->exists();
        }

        $author = clone ($post->relationLoaded('author') ? $post->author : ($post->author()->first() ?? new \App\Models\User));

        return [
            'id' => $post->id,
            'user_id' => $post->id_user,
            'name' => $author->name ?? 'Pengguna',
            'username' => $author->username ?? null,
            'profile_url' => $author->username ? route('profile.by_username', ['username' => ltrim($author->username, '@')]) : '#',
            'handle' => $author->username ? '@' . ltrim($author->username, '@') : '@pengguna',
            'location' => $author->kota ?: 'Online',
            'time' => $post->created_at ? Carbon::parse($post->created_at)->locale('id')->diffForHumans() : 'Baru saja',
            'absolute_time' => $post->created_at ? Carbon::parse($post->created_at)->timezone('Asia/Jakarta')->locale('id')->translatedFormat('d M Y, H:i') : 'Baru saja',
            'book' => $post->judul_buku_dibahas,
            'body' => $post->pesan,
            'comments' => $comments_label,
            'likes_base' => $likesCount,
            'likes_label' => $likes_label,
            'liked' => $liked,
            'bookmarked' => $bookmarked,
            'avatar_url' => !empty($author->foto_profil) ? asset('storage/' . $author->foto_profil) : ($author->avatar_url ?? null),
            'avatar_from' => '#FFDDAF',
            'avatar_to' => '#C7E7FF',
            'tag' => $post->tag ?: 'Post',
            'klub' => $post->relationLoaded('club') && $post->club ? $post->club->nama_klub : null,
            'media' => $firstAttachment['path'] ?? $post->media ?? null,
            'media_url' => $firstAttachment['url'] ?? ($post->media ? asset('storage/' . $post->media) : null),
            'media_type' => $firstAttachment['type'] ?? $post->media_type ?? null,
            'media_original_name' => $firstAttachment['original_name'] ?? $post->media_original_name ?? null,
            'media_size' => $firstAttachment['size'] ?? $post->media_size ?? null,
            'attachments' => $payloadAttachments,
        ];
    }
}
