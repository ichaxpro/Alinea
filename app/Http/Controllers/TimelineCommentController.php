<?php

namespace App\Http\Controllers;

use App\Models\TimelinePost;
use App\Models\TimelineComment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;
use App\Services\TimelineFormatterService;

class TimelineCommentController extends Controller
{
    public function __construct(
        protected TimelineFormatterService $timelineFormatterService
    ) {}

    public function index(Request $request, TimelinePost $post)
    {
        $currentUser = Auth::user();
        $limit = $request->query('limit');

        $query = $post->comments()
            ->with(['author:id,name,username,foto_profil', 'attachments', 'likes'])
            ->orderBy('created_at');

        $total = $query->count();

        if ($limit) {
            $query->limit((int) $limit);
        }

        $comments = $query->get()
            ->map(function ($comment) use ($currentUser) {
                // Formatting is slightly different from community timeline, let's keep the existing format.
                $attachments = collect($comment->attachments)->map(fn($att) => [
                    'url' => asset('storage/' . $att->path),
                    'type' => $att->type,
                ])->all();
                
                $likesCount = $comment->likes->count();
                $liked = $currentUser ? $comment->likes->contains('id_user', $currentUser->id) : false;
                $likes_label = $likesCount >= 1000 ? round($likesCount/1000, 1) . 'K' : (string) $likesCount;
                
                return [
                    'id' => $comment->id,
                    'name' => $comment->author->name ?? 'Pengguna',
                    'username' => $comment->author->username,
                    'profile_url' => $comment->author->username ? route('profile.by_username', ['username' => ltrim($comment->author->username, '@')]) : '#',
                    'handle' => $comment->author->username ? '@' . ltrim($comment->author->username, '@') : '@pengguna',
                    'avatar_url' => $comment->author->foto_profil ? asset('storage/' . $comment->author->foto_profil) : null,
                    'body' => $comment->isi_komentar,
                    'attachments' => $attachments,
                    'time' => $comment->created_at ? Carbon::parse($comment->created_at)->locale('id')->diffForHumans() : 'Baru saja',
                    'absolute_time' => $comment->created_at ? Carbon::parse($comment->created_at)->timezone('Asia/Jakarta')->locale('id')->translatedFormat('d M Y, H:i') : 'Baru saja',
                    'likes_base' => $likesCount,
                    'likes_label' => $likes_label,
                    'liked' => $liked,
                ];
            });

        return response()->json([
            'comments' => $comments,
            'total' => $total,
        ]);
    }

    public function store(Request $request, TimelinePost $post)
    {
        $currentUser = Auth::user();
        if (!$currentUser) {
            return response()->json(['message' => 'Silakan login terlebih dahulu.'], 401);
        }

        $validated = $request->validate([
            'isi_komentar' => ['required', 'string', 'max:500'],
        ]);

        $comment = TimelineComment::create([
            'id_post' => $post->id,
            'id_user' => $currentUser->id,
            'isi_komentar' => $validated['isi_komentar'],
        ]);

        $comment->load(['author']);

        if ($post->id_user !== $currentUser->id) {
            $author = \App\Models\User::find($post->id_user);
            if ($author) {
                $author->notify(new \App\Notifications\PostCommented($currentUser, $post, $comment));
            }
        }

        return response()->json([
            'message' => 'Komentar berhasil dikirim.',
            'comment' => [
                'id' => $comment->id,
                'name' => $currentUser->name,
                'username' => $currentUser->username,
                'profile_url' => $currentUser->username ? route('profile.by_username', ['username' => ltrim($currentUser->username, '@')]) : '#',
                'handle' => $currentUser->username ? '@' . ltrim($currentUser->username, '@') : '@pengguna',
                'avatar_url' => $currentUser->foto_profil ? asset('storage/' . $currentUser->foto_profil) : null,
                'body' => $comment->isi_komentar,
                'attachments' => [],
                'time' => 'Baru saja',
                'absolute_time' => Carbon::now()->timezone('Asia/Jakarta')->locale('id')->translatedFormat('d M Y, H:i'),
                'likes_base' => 0,
                'likes_label' => '0',
                'liked' => false,
            ],
            'comments_count' => $post->comments()->count(),
        ], 201);
    }
}
