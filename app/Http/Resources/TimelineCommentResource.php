<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TimelineCommentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $author = clone ($this->relationLoaded('author') ? $this->author : ($this->author()->first() ?? auth()->user() ?? new \App\Models\User));
        $attachments = $this->relationLoaded('attachments') ? $this->attachments : $this->attachments()->get();
        $payloadAttachments = AttachmentResource::collection($attachments)->resolve();
        $firstAttachment = $payloadAttachments[0] ?? null;

        return [
            'id' => $this->id,
            'user_id' => $this->id_user,
            'name' => $author->name ?? 'Pengguna',
            'username' => $author->username,
            'profile_url' => $author->username ? route('profile.by_username', ['username' => ltrim($author->username, '@')]) : '#',
            'handle' => $author->username ? '@' . ltrim($author->username, '@') : '@pengguna',
            'avatar_url' => $author->avatar_url ?? null,
            'body' => $this->isi_komentar,
            'media' => $firstAttachment['path'] ?? $this->media,
            'media_url' => $firstAttachment['url'] ?? ($this->media ? asset('storage/' . $this->media) : null),
            'media_type' => $firstAttachment['type'] ?? $this->media_type,
            'media_original_name' => $firstAttachment['original_name'] ?? $this->media_original_name,
            'media_size' => $firstAttachment['size'] ?? $this->media_size,
            'attachments' => $payloadAttachments,
            'time' => $this->created_at ? \Carbon\Carbon::parse($this->created_at)->locale('id')->diffForHumans() : 'Baru saja',
            'absolute_time' => $this->created_at ? \Carbon\Carbon::parse($this->created_at)->timezone('Asia/Jakarta')->locale('id')->translatedFormat('d M Y, H:i') : 'Baru saja',
        ];
    }
}
