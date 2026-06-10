<?php

namespace App\Services;

use App\Models\TimelineComment;
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
}
