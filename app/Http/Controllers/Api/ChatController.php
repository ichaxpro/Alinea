<?php

namespace App\Http\Controllers\Api;

use App\Events\MessageDeleted;
use App\Events\MessageSent;
use App\Events\TypingIndicator;
use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class ChatController extends Controller
{
    // ── Helpers ───────────────────────────────────────────────────────────────

    private function parseConversationId(string $id): array
    {
        return array_map('intval', explode('_', $id));
    }

    private function buildConversationId(int $a, int $b): string
    {
        $ids = [$a, $b];
        sort($ids);
        return implode('_', $ids);
    }

    private function authorize(string $conversationId): array
    {
        $ids = $this->parseConversationId($conversationId);
        abort_if(!in_array(Auth::id(), $ids), 403, 'Unauthorized');

        return $ids;
    }

    private function formatUser(User $user): array
    {
        return [
            'id'         => $user->id,
            'name'       => $user->name,
            'username'   => $user->username ?? '',
            'avatar_url' => $user->avatar_url,
            'initial'    => strtoupper(substr($user->name, 0, 1)),
        ];
    }

    private function formatMessage(Message $msg, bool $isMine): array
    {
        $isDeleted = !is_null($msg->deleted_at);

        return [
            'id'                  => $msg->id,
            'content'             => $isDeleted ? null : $msg->message,
            'is_mine'             => $isMine,
            'is_deleted'          => $isDeleted,
            'sender_id'           => $msg->sender_id,
            'created_at'          => $msg->created_at,
            'media_url'           => (!$isDeleted && $msg->media_url)
                                        ? Storage::disk('public')->url($msg->media_url)
                                        : null,
            'media_type'          => $isDeleted ? null : $msg->media_type,
            'media_original_name' => $isDeleted ? null : $msg->media_original_name,
        ];
    }

    // ── Conversations ─────────────────────────────────────────────────────────

    public function conversations()
    {
        $authId = Auth::id();

        $messages = Message::withTrashed()
            ->where('sender_id', $authId)
            ->orWhere('receiver_id', $authId)
            ->get(['sender_id', 'receiver_id']);

        $partnerIds = $messages
            ->map(fn($m) => $m->sender_id === $authId ? $m->receiver_id : $m->sender_id)
            ->unique()
            ->values();

        $conversations = $partnerIds->map(function ($partnerId) use ($authId) {
            $partner = clone User::find($partnerId);
            if (!$partner) return null;

            $convId = $this->buildConversationId($authId, $partnerId);

            $block = \Illuminate\Support\Facades\DB::table('blocks')
                ->where(function($q) use ($authId, $partnerId) {
                    $q->where('user_id', $authId)->where('blocked_user_id', $partnerId);
                })->orWhere(function($q) use ($authId, $partnerId) {
                    $q->where('user_id', $partnerId)->where('blocked_user_id', $authId);
                })->first();

            $isBlockedByMe = $block && $block->user_id === $authId;
            $isBlockedByThem = $block && $block->user_id === $partnerId;

            $lastMsg = Message::withTrashed()->where(
                fn($q) => $q->where('sender_id', $authId)->where('receiver_id', $partnerId)
            )->orWhere(
                fn($q) => $q->where('sender_id', $partnerId)->where('receiver_id', $authId)
            )->latest()->first();

            $unread = Message::where('sender_id', $partnerId)
                ->where('receiver_id', $authId)
                ->whereNull('read_at')
                ->count();

            $previewText = 'Belum ada pesan';
            if ($lastMsg) {
                if ($lastMsg->deleted_at) {
                    $previewText = 'Pesan dihapus';
                } elseif ($lastMsg->media_type === 'image') {
                    $previewText = '📷 Foto';
                } elseif ($lastMsg->media_type === 'audio') {
                    $previewText = '🎵 Audio';
                } elseif ($lastMsg->media_type === 'video') {
                    $previewText = '🎬 Video';
                } else {
                    $previewText = $lastMsg->message ?? 'Belum ada pesan';
                }
            }

            return [
                'id'                 => $convId,
                'other_user'         => $this->formatUser($partner),
                'is_blocked_by_me'   => $isBlockedByMe,
                'is_blocked_by_them' => $isBlockedByThem,
                'last_message'       => $lastMsg ? [
                    'content'    => $previewText,
                    'created_at' => $lastMsg->created_at,
                ] : null,
                'unread_count'       => $unread,
            ];
        })
        ->filter()
        ->sortByDesc(fn($c) => $c['last_message']['created_at'] ?? '')
        ->values();

        return response()->json(['data' => $conversations]);
    }

    public function startConversation(Request $request)
    {
        $request->validate(['user_id' => 'required|exists:users,id']);

        $partnerId = (int) $request->user_id;
        abort_if($partnerId === Auth::id(), 422, 'Cannot chat with yourself');

        $partner = clone User::findOrFail($partnerId);
        $convId  = $this->buildConversationId(Auth::id(), $partnerId);

        $block = \Illuminate\Support\Facades\DB::table('blocks')
            ->where(function($q) use ($partnerId) {
                $q->where('user_id', Auth::id())->where('blocked_user_id', $partnerId);
            })->orWhere(function($q) use ($partnerId) {
                $q->where('user_id', $partnerId)->where('blocked_user_id', Auth::id());
            })->first();

        $isBlockedByMe = $block && $block->user_id === Auth::id();
        $isBlockedByThem = $block && $block->user_id === $partnerId;

        $lastMsg = Message::withTrashed()->where(
            fn($q) => $q->where('sender_id', Auth::id())->where('receiver_id', $partnerId)
        )->orWhere(
            fn($q) => $q->where('sender_id', $partnerId)->where('receiver_id', Auth::id())
        )->latest()->first();

        return response()->json([
            'data' => [
                'id'                 => $convId,
                'other_user'         => $this->formatUser($partner),
                'is_blocked_by_me'   => $isBlockedByMe,
                'is_blocked_by_them' => $isBlockedByThem,
                'last_message'       => $lastMsg ? [
                    'content'    => $lastMsg->message ?? '',
                    'created_at' => $lastMsg->created_at,
                ] : null,
                'unread_count' => 0,
            ],
        ]);
    }

    // ── Messages ──────────────────────────────────────────────────────────────

    public function messages(string $id)
    {
        [$userA, $userB] = $this->authorize($id);
        $authId = Auth::id();

        // Use withTrashed so deleted messages still appear as "Pesan dihapus"
        $paginator = Message::withTrashed()->where(
            fn($q) => $q->where('sender_id', $userA)->where('receiver_id', $userB)
        )->orWhere(
            fn($q) => $q->where('sender_id', $userB)->where('receiver_id', $userA)
        )->orderBy('created_at', 'desc')->paginate(30);

        $paginator->getCollection()->transform(
            fn($msg) => $this->formatMessage($msg, $msg->sender_id === $authId)
        );

        return response()->json($paginator);
    }

    public function sendMessage(Request $request, string $id)
    {
        $request->validate([
            'content' => 'nullable|string|max:5000',
            'media'   => 'nullable|file|max:102400|mimes:jpeg,jpg,png,gif,webp,mp3,ogg,wav,mp4,mov,webm,m4a,aac',
        ]);

        abort_if(
            empty($request->input('content')) && !$request->hasFile('media'),
            422,
            'Pesan atau media wajib diisi.'
        );

        [$userA, $userB] = $this->authorize($id);
        $authId     = Auth::id();
        $receiverId = $authId === $userA ? $userB : $userA;

        $isBlocked = \Illuminate\Support\Facades\DB::table('blocks')
            ->where(function($q) use ($authId, $receiverId) {
                $q->where('user_id', $authId)->where('blocked_user_id', $receiverId);
            })->orWhere(function($q) use ($authId, $receiverId) {
                $q->where('user_id', $receiverId)->where('blocked_user_id', $authId);
            })->exists();
        
        abort_if($isBlocked, 403, 'Percakapan tidak tersedia karena pengguna diblokir.');

        $mediaUrl          = null;
        $mediaType         = null;
        $mediaOriginalName = null;

        if ($request->hasFile('media')) {
            $file              = $request->file('media');
            $mediaOriginalName = $file->getClientOriginalName();
            $mime              = $file->getMimeType();

            if (str_starts_with($mime, 'image/'))      $mediaType = 'image';
            elseif (str_starts_with($mime, 'audio/'))  $mediaType = 'audio';
            elseif (str_starts_with($mime, 'video/'))  $mediaType = 'video';

            $mediaUrl = $file->store("chat-media/{$id}", 'public');
        }

        $message = Message::create([
            'sender_id'           => $authId,
            'receiver_id'         => $receiverId,
            'message'             => $request->input('content', ''),
            'media_url'           => $mediaUrl,
            'media_type'          => $mediaType,
            'media_original_name' => $mediaOriginalName,
        ]);

        $payload = $this->formatMessage($message, false);
        $payload['sender_id'] = $authId;

        broadcast(new MessageSent($id, $payload, $authId))->toOthers();

        return response()->json(['data' => $this->formatMessage($message, true)]);
    }

    /**
     * DELETE /api/chat/conversations/{id}/messages/{msgId}
     * Soft-delete a message (only the sender can delete their own).
     */
    public function deleteMessage(string $id, int $msgId)
    {
        $this->authorize($id);

        $message = Message::where('id', $msgId)
            ->where('sender_id', Auth::id())
            ->firstOrFail();

        $message->delete(); // soft delete

        broadcast(new MessageDeleted($id, $msgId))->toOthers();

        return response()->json(['ok' => true]);
    }

    public function markAsRead(string $id)
    {
        [$userA, $userB] = $this->authorize($id);
        $authId   = Auth::id();
        $senderId = $authId === $userA ? $userB : $userA;

        Message::where('sender_id', $senderId)
            ->where('receiver_id', $authId)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json(['ok' => true]);
    }

    public function typing(Request $request, string $id)
    {
        $this->authorize($id);

        broadcast(new TypingIndicator($id, Auth::id(), $request->boolean('is_typing')))->toOthers();

        return response()->json(['ok' => true]);
    }

    // ── User Detail Panel ──────────────────────────────────────────────────────

    /**
     * GET /api/chat/conversations/{id}/media
     * Return all non-deleted image & video messages in the conversation.
     */
    public function conversationMedia(string $id)
    {
        [$userA, $userB] = $this->authorize($id);

        $media = Message::withTrashed()
            ->where(function ($q) use ($userA, $userB) {
                // Both directions wrapped together so the filters below
                // apply to the whole group, not just one branch.
                $q->where(function ($q2) use ($userA, $userB) {
                    $q2->where('sender_id', $userA)->where('receiver_id', $userB);
                })->orWhere(function ($q2) use ($userA, $userB) {
                    $q2->where('sender_id', $userB)->where('receiver_id', $userA);
                });
            })
            ->whereNotNull('media_url')
            ->whereNull('deleted_at')
            ->whereIn('media_type', ['image', 'video'])
            ->orderBy('created_at', 'desc')
            ->get(['id', 'media_url', 'media_type', 'media_original_name', 'created_at']);

        
        $formatted = $media->map(fn($m) => [
            'id' => $m->id,
            'url' => Storage::disk('public')->url($m->media_url),
            'type' => $m->media_type,
            'name' => $m->media_original_name,
            'created_at' => $m->created_at,
        ]);

        return response()->json(['data' => $formatted]);
    }

    /**
     * DELETE /api/chat/conversations/{id}
     * Soft-delete all messages in this conversation for the auth user.
     */
    public function deleteConversation(string $id)
    {
        [$userA, $userB] = $this->authorize($id);
        $authId  = Auth::id();
        $otherId = $authId === $userA ? $userB : $userA;

        // Soft-delete messages the auth user sent
        Message::where('sender_id', $authId)
            ->where('receiver_id', $otherId)
            ->delete();

        // Soft-delete messages the auth user received (so chat disappears for them)
        Message::where('sender_id', $otherId)
            ->where('receiver_id', $authId)
            ->delete();

        return response()->json(['ok' => true]);
    }

    /**
     * POST /api/users/{userId}/report
     */
    public function reportUser(Request $request, int $userId)
    {
        $request->validate(['reason' => 'nullable|string|max:500']);

        $reporterId = Auth::id();

        \App\Models\Report::create([
            'reporter_id' => $reporterId,
            'reported_user_id' => $userId,
            'reason' => $request->input('reason'),
            'status' => 'pending',
        ]);

        \Log::info('User report created', [
            'reporter' => $reporterId,
            'reported' => $userId,
            'reason'   => $request->input('reason'),
        ]);

        return response()->json(['ok' => true]);
    }

    /**
     * POST /api/users/{userId}/block
     */
    public function blockUser(int $userId)
    {
        $authId = Auth::id();
        
        abort_if($authId === $userId, 422, 'Cannot block yourself');

        $block = \App\Models\Block::where('user_id', $authId)
            ->where('blocked_user_id', $userId)
            ->first();

        if ($block) {
            $block->delete();
            $action = 'unblocked';
        } else {
            \App\Models\Block::create([
                'user_id' => $authId,
                'blocked_user_id' => $userId,
            ]);
            $action = 'blocked';

            // Sever any follows
            \App\Models\Follow::where(function ($q) use ($authId, $userId) {
                $q->where('follower_id', $authId)->where('following_id', $userId);
            })->orWhere(function ($q) use ($authId, $userId) {
                $q->where('follower_id', $userId)->where('following_id', $authId);
            })->delete();
        }

        \Log::info("User {$action}", [
            'actor' => $authId,
            'target' => $userId,
        ]);

        $convId = $this->buildConversationId($authId, $userId);
        broadcast(new \App\Events\ConversationBlockUpdated($convId, $authId, $action === 'blocked'))->toOthers();

        return response()->json(['ok' => true, 'action' => $action]);
    }
}
