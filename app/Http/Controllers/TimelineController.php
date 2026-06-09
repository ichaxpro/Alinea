<?php

namespace App\Http\Controllers;

use App\Models\TimelinePost;
use App\Models\TimelineComment;
use App\Models\TimelineCommentLike;
use App\Models\TimelineLike;
use App\Models\PostBookmark;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use App\Services\TrendingService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rule;

class TimelineController extends Controller
{
    private function detectMediaType(?string $mime): string
    {
        $mime = $mime ?: '';
        if (str_starts_with($mime, 'image/')) return 'image';
        if (str_starts_with($mime, 'video/')) return 'video';
        return 'file';
    }

    public function __construct(
        protected TrendingService $trendingService
    ) {}

    public function index(Request $request)
    {
        $currentUser = Auth::user();
        $tab = $request->query('tab', 'untukmu');
        
        $query = TimelinePost::with(['author', 'attachments', 'likes'])
            ->withCount('comments')
            ->whereNull('id_klub');
        
        $activeBook = $request->query('book');
        $activeTag = $request->query('tag_filter');

        if ($activeBook) {
            $query->where(DB::raw('LOWER(TRIM(judul_buku_dibahas))'), strtolower(trim($activeBook)));
        }

        if ($activeTag) {
            $query->where('tag', $activeTag);
        }

        $query->orderByDesc('created_at');

        if ($tab === 'mengikuti' && $currentUser) {
            $followingIds = $currentUser->following()->pluck('following_id');
            $query->whereIn('id_user', $followingIds);
        }

        $posts = $query->get()
            ->map(function ($post) use ($currentUser) {
                $payloadAttachments = $post->attachments->map(fn($attachment) => [
                    'id' => $attachment->id,
                    'path' => $attachment->path,
                    'url' => asset('storage/' . $attachment->path),
                    'type' => $attachment->type,
                    'original_name' => $attachment->original_name,
                    'size' => $attachment->size,
                ])->values()->all();

                $firstAttachment = $payloadAttachments[0] ?? null;
                $likesCount = $post->likes->count();
                $liked = $currentUser ? $post->likes->contains('id_user', $currentUser->id) : false;
                
                // Format likes_label e.g. 1.2K
                $likes_label = $likesCount >= 1000 ? round($likesCount/1000, 1) . 'K' : (string) $likesCount;
                $comments_label = $post->comments_count >= 1000 ? round($post->comments_count/1000, 1) . 'K' : (string) $post->comments_count;

                $bookmarked = $currentUser ? PostBookmark::where('id_post', $post->id)->where('id_user', $currentUser->id)->exists() : false;

                return [
                    'id' => $post->id,
                    'user_id' => $post->id_user,
                    'name' => $post->author->name ?? 'Pengguna',
                    'username' => $post->author->username,
                    'profile_url' => $post->author->username ? route('profile.by_username', ['username' => ltrim($post->author->username, '@')]) : '#',
                    'handle' => $post->author->username ? '@' . ltrim($post->author->username, '@') : '@pengguna',
                    'location' => $post->author->kota ?: 'Online',
                    'time' => $post->created_at ? \Carbon\Carbon::parse($post->created_at)->locale('id')->diffForHumans() : 'Baru saja',
                    'absolute_time' => $post->created_at ? \Carbon\Carbon::parse($post->created_at)->timezone('Asia/Jakarta')->locale('id')->translatedFormat('d M Y, H:i') : 'Baru saja',
                    'book' => $post->judul_buku_dibahas,
                    'body' => $post->pesan,
                    'comments' => $comments_label,
                    'likes_base' => $likesCount,
                    'likes_label' => $likes_label,
                    'liked' => $liked,
                    'bookmarked' => $bookmarked,
                    'avatar_url' => $post->author->foto_profil ? asset('storage/' . $post->author->foto_profil) : ($post->author->avatar_url ?? null),
                    'avatar_from' => '#FFDDAF', // Default gradients for personal posts
                    'avatar_to' => '#C7E7FF',
                    'tag' => $post->tag ?: 'Post',
                    'media' => $firstAttachment['path'] ?? $post->media ?? null,
                    'media_url' => $firstAttachment['url'] ?? ($post->media ? asset('storage/' . $post->media) : null),
                    'media_type' => $firstAttachment['type'] ?? $post->media_type ?? null,
                    'attachments' => $payloadAttachments,
                ];
            });

        $trendingItems = collect($this->trendingService->getWeeklyTrending())
            ->map(fn ($item) => [
                $item['judul'],
                $item['count'] . ' postingan',
                $activeBook && strtolower(trim($activeBook)) === strtolower(trim($item['judul']))
                    ? route('timeline_home')
                    : route('timeline_home', ['book' => $item['judul']]),
            ])
            ->all();

        return view('timeline_home', compact('posts', 'trendingItems', 'activeBook', 'activeTag'));
    }

    public function show(TimelinePost $post)
    {
        $currentUser = Auth::user();
        
        $post->load(['author', 'attachments', 'likes', 'club']);
        $post->loadCount('comments');

        $payloadAttachments = $post->attachments->map(fn($attachment) => [
            'id' => $attachment->id,
            'path' => $attachment->path,
            'url' => asset('storage/' . $attachment->path),
            'type' => $attachment->type,
            'original_name' => $attachment->original_name,
            'size' => $attachment->size,
        ])->values()->all();

        $firstAttachment = $payloadAttachments[0] ?? null;
        $likesCount = $post->likes->count();
        $liked = $currentUser ? $post->likes->contains('id_user', $currentUser->id) : false;
        
        $likes_label = $likesCount >= 1000 ? round($likesCount/1000, 1) . 'K' : (string) $likesCount;
        $comments_label = $post->comments_count >= 1000 ? round($post->comments_count/1000, 1) . 'K' : (string) $post->comments_count;

        $bookmarked = $currentUser ? PostBookmark::where('id_post', $post->id)->where('id_user', $currentUser->id)->exists() : false;

        $formattedPost = [
            'id' => $post->id,
            'user_id' => $post->id_user,
            'name' => $post->author->name ?? 'Pengguna',
            'handle' => $post->author->username ? '@' . ltrim($post->author->username, '@') : '@pengguna',
            'location' => $post->author->kota ?: 'Online',
            'time' => $post->created_at ? \Carbon\Carbon::parse($post->created_at)->locale('id')->diffForHumans() : 'Baru saja',
            'absolute_time' => $post->created_at ? \Carbon\Carbon::parse($post->created_at)->timezone('Asia/Jakarta')->locale('id')->translatedFormat('d M Y, H:i') : 'Baru saja',
            'book' => $post->judul_buku_dibahas,
            'body' => $post->pesan,
            'comments' => $comments_label,
            'likes_base' => $likesCount,
            'likes_label' => $likes_label,
            'liked' => $liked,
            'bookmarked' => $bookmarked,
            'avatar_url' => $post->author->foto_profil ? asset('storage/' . $post->author->foto_profil) : ($post->author->avatar_url ?? null),
            'avatar_from' => '#FFDDAF',
            'avatar_to' => '#C7E7FF',
            'tag' => $post->tag ?: 'Post',
            'klub' => $post->club ? $post->club->nama : null,
            'media' => $firstAttachment['path'] ?? $post->media ?? null,
            'media_url' => $firstAttachment['url'] ?? ($post->media ? asset('storage/' . $post->media) : null),
            'media_type' => $firstAttachment['type'] ?? $post->media_type ?? null,
            'media_original_name' => $firstAttachment['original_name'] ?? $post->media_original_name ?? null,
            'media_size' => $firstAttachment['size'] ?? $post->media_size ?? null,
            'attachments' => $payloadAttachments,
        ];

        $trendingItems = collect($this->trendingService->getWeeklyTrending())
            ->map(fn ($item) => [
                $item['judul'],
                $item['count'] . ' postingan',
                route('timeline_home', ['book' => $item['judul']]),
            ])
            ->all();

        return view('timeline_post', [
            'post' => $formattedPost,
            'trendingItems' => $trendingItems,
            'isOwnProfile' => $currentUser && $currentUser->id === $post->id_user
        ]);
    }

    public function store(Request $request)
    {
        $currentUser = Auth::user();
        if (!$currentUser) {
            return response()->json(['message' => 'Silakan login terlebih dahulu.'], 401);
        }

        $validated = $request->validate([
            'judul_buku_dibahas' => ['nullable', 'string', 'max:120'],
            'pesan' => ['required', 'string', 'max:500'],
            'tag' => ['nullable', 'string', 'max:30'],
            'media' => ['nullable', 'array', 'max:4'],
            'media.*' => ['file', 'max:102400'], // max 100MB per file
        ]);

        $files = $request->file('media', []);
        $attachments = [];
        foreach (array_values($files) as $index => $file) {
            if (!$file) continue;
            
            $path = $file->store('timeline_media', 'public');
            $attachments[] = [
                'path' => $path,
                'type' => $this->detectMediaType($file->getMimeType()),
                'original_name' => $file->getClientOriginalName(),
                'size' => $file->getSize(),
                'sort_order' => $index,
            ];
        }

        $post = TimelinePost::create([
            'id_user' => $currentUser->id,
            'id_klub' => null, // Global post
            'judul_buku_dibahas' => $validated['judul_buku_dibahas'] ?? null,
            'pesan' => $validated['pesan'],
            'tag' => $validated['tag'] ?? 'Post',
            'media' => $attachments[0]['path'] ?? null,
            'media_type' => $attachments[0]['type'] ?? null,
            'media_original_name' => $attachments[0]['original_name'] ?? null,
            'media_size' => $attachments[0]['size'] ?? null,
        ]);

        if (!empty($attachments)) {
            $post->attachments()->createMany($attachments);
        }

        $post->load(['author', 'attachments', 'likes']);

        if (!empty($validated['judul_buku_dibahas'])) {
            $now = \Carbon\Carbon::now();
            $startOfWeek = $now->copy()->startOfWeek();
            Cache::forget("trending_weekly_{$startOfWeek->year}_W{$startOfWeek->weekOfYear}");
        }

        return response()->json([
            'message' => 'Postingan berhasil diunggah.',
            'post' => [
                'id' => $post->id,
                'user_id' => $currentUser->id,
                'name' => $currentUser->name,
                'username' => $currentUser->username,
                'profile_url' => $currentUser->username ? route('profile.by_username', ['username' => ltrim($currentUser->username, '@')]) : '#',
                'handle' => $currentUser->username ? '@' . ltrim($currentUser->username, '@') : '@pengguna',
                'location' => $currentUser->kota ?: 'Online',
                'time' => 'Baru saja',
                'absolute_time' => \Carbon\Carbon::now()->timezone('Asia/Jakarta')->locale('id')->translatedFormat('d M Y, H:i'),
                'book' => $post->judul_buku_dibahas,
                'body' => $post->pesan,
                'comments' => '0',
                'likes_base' => 0,
                'likes_label' => '0',
                'liked' => false,
                'avatar_url' => $currentUser->foto_profil ? asset('storage/' . $currentUser->foto_profil) : ($currentUser->avatar_url ?? null),
                'avatar_from' => '#FFDDAF',
                'avatar_to' => '#C7E7FF',
                'tag' => $post->tag ?: 'Post',
                'attachments' => array_map(function ($attachment) {
                    return [
                        'path' => $attachment['path'],
                        'url' => asset('storage/' . $attachment['path']),
                        'type' => $attachment['type'],
                    ];
                }, $attachments),
            ],
        ], 201);
    }

    public function destroy(TimelinePost $post)
    {
        $currentUser = Auth::user();
        if (!$currentUser) {
            return response()->json(['message' => 'Silakan login terlebih dahulu.'], 401);
        }

        // Only allow owner or admin to delete
        if ($post->id_user !== $currentUser->id && $currentUser->role !== 'admin') {
            return response()->json(['message' => 'Anda tidak berhak menghapus unggahan ini.'], 403);
        }

        $post->delete();

        return response()->json([
            'message' => 'Unggahan berhasil dihapus.',
        ]);
    }

    public function toggleLike(TimelinePost $post)
    {
        $currentUser = Auth::user();
        if (!$currentUser) {
            return response()->json(['message' => 'Silakan login terlebih dahulu.'], 401);
        }

        $like = TimelineLike::where('id_post', $post->id)->where('id_user', $currentUser->id)->first();
        if ($like) {
            $like->delete();
            $liked = false;
            if ($post->id_user !== $currentUser->id) {
                $author = \App\Models\User::find($post->id_user);
                if ($author) {
                    $author->notifications()
                        ->where('type', \App\Notifications\PostLiked::class)
                        ->where('data->user_id', $currentUser->id)
                        ->where('data->post_id', $post->id)
                        ->delete();
                }
            }
        } else {
            TimelineLike::create(['id_post' => $post->id, 'id_user' => $currentUser->id]);
            $liked = true;
            if ($post->id_user !== $currentUser->id) {
                $author = \App\Models\User::find($post->id_user);
                if ($author) {
                    $author->notify(new \App\Notifications\PostLiked($currentUser, $post));
                }
            }
        }

        $likesCount = TimelineLike::where('id_post', $post->id)->count();
        $likes_label = $likesCount >= 1000 ? round($likesCount/1000, 1) . 'K' : (string) $likesCount;

        return response()->json([
            'liked' => $liked,
            'likes_base' => $likesCount,
            'likes_label' => $likes_label,
        ]);
    }

    public function comments(Request $request, TimelinePost $post)
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
                    'absolute_time' => $comment->created_at ? \Carbon\Carbon::parse($comment->created_at)->timezone('Asia/Jakarta')->locale('id')->translatedFormat('d M Y, H:i') : 'Baru saja',
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

    public function storeComment(Request $request, TimelinePost $post)
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
                'absolute_time' => \Carbon\Carbon::now()->timezone('Asia/Jakarta')->locale('id')->translatedFormat('d M Y, H:i'),
                'likes_base' => 0,
                'likes_label' => '0',
                'liked' => false,
            ],
            'comments_count' => $post->comments()->count(),
        ], 201);
    }

    public function toggleCommentLike(TimelineComment $comment)
    {
        $currentUser = Auth::user();
        if (!$currentUser) {
            return response()->json(['message' => 'Silakan login terlebih dahulu.'], 401);
        }

        $like = TimelineCommentLike::where('id_comment', $comment->id)->where('id_user', $currentUser->id)->first();
        if ($like) {
            $like->delete();
            $liked = false;
        } else {
            TimelineCommentLike::create(['id_comment' => $comment->id, 'id_user' => $currentUser->id]);
            $liked = true;
        }

        $likesCount = TimelineCommentLike::where('id_comment', $comment->id)->count();
        $likes_label = $likesCount >= 1000 ? round($likesCount/1000, 1) . 'K' : (string) $likesCount;

        return response()->json([
            'liked' => $liked,
            'likes_base' => $likesCount,
            'likes_label' => $likes_label,
        ]);
    }

    public function toggleBookmark(TimelinePost $post)
    {
        $currentUser = Auth::user();
        if (!$currentUser) {
            return response()->json(['message' => 'Silakan login terlebih dahulu.'], 401);
        }

        $bookmark = PostBookmark::where('id_post', $post->id)->where('id_user', $currentUser->id)->first();
        if ($bookmark) {
            $bookmark->delete();
            $bookmarked = false;
        } else {
            PostBookmark::create(['id_post' => $post->id, 'id_user' => $currentUser->id]);
            $bookmarked = true;
        }

        return response()->json([
            'bookmarked' => $bookmarked,
        ]);
    }

    public function reportPost(Request $request, TimelinePost $post)
    {
        $currentUser = Auth::user();
        if (!$currentUser) {
            return response()->json(['message' => 'Silakan login terlebih dahulu.'], 401);
        }

        $validated = $request->validate([
            'reason' => ['required', 'string', 'min:8'],
        ]);

        \App\Models\ReportPost::create([
            'reporter_id' => $currentUser->id,
            'post_id' => $post->id,
            'reason' => $validated['reason'],
        ]);

        return response()->json([
            'message' => 'Laporan berhasil dikirim dan akan segera kami tinjau.',
        ]);
    }

    public function simpanan(Request $request)
    {
        $currentUser = Auth::user();
        if (!$currentUser) {
            return redirect()->route('login');
        }

        $query = TimelinePost::with(['author', 'attachments', 'likes'])
            ->withCount('comments')
            ->whereHas('bookmarkedBy', function ($q) use ($currentUser) {
                $q->where('users.id', $currentUser->id);
            })
            ->orderByDesc('created_at');

        $posts = $query->get()
            ->map(function ($post) use ($currentUser) {
                $payloadAttachments = $post->attachments->map(fn($attachment) => [
                    'id' => $attachment->id,
                    'path' => $attachment->path,
                    'url' => asset('storage/' . $attachment->path),
                    'type' => $attachment->type,
                    'original_name' => $attachment->original_name,
                    'size' => $attachment->size,
                ])->values()->all();

                $firstAttachment = $payloadAttachments[0] ?? null;
                $likesCount = $post->likes->count();
                $liked = $currentUser ? $post->likes->contains('id_user', $currentUser->id) : false;
                
                $likes_label = $likesCount >= 1000 ? round($likesCount/1000, 1) . 'K' : (string) $likesCount;
                $comments_label = $post->comments_count >= 1000 ? round($post->comments_count/1000, 1) . 'K' : (string) $post->comments_count;

                return [
                    'id' => $post->id,
                    'user_id' => $post->id_user,
                    'name' => $post->author->name ?? 'Pengguna',
                    'username' => $post->author->username,
                    'profile_url' => $post->author->username ? route('profile.by_username', ['username' => ltrim($post->author->username, '@')]) : '#',
                    'handle' => $post->author->username ? '@' . ltrim($post->author->username, '@') : '@pengguna',
                    'location' => $post->author->kota ?: 'Online',
                    'time' => $post->created_at ? \Carbon\Carbon::parse($post->created_at)->locale('id')->diffForHumans() : 'Baru saja',
                    'absolute_time' => $post->created_at ? \Carbon\Carbon::parse($post->created_at)->timezone('Asia/Jakarta')->locale('id')->translatedFormat('d M Y, H:i') : 'Baru saja',
                    'book' => $post->judul_buku_dibahas,
                    'body' => $post->pesan,
                    'comments' => $comments_label,
                    'likes_base' => $likesCount,
                    'likes_label' => $likes_label,
                    'liked' => $liked,
                    'bookmarked' => true,
                    'klub' => $post->club ? $post->club->nama : null,
                    'avatar_url' => $post->author->foto_profil ? asset('storage/' . $post->author->foto_profil) : ($post->author->avatar_url ?? null),
                    'avatar_from' => '#FFDDAF',
                    'avatar_to' => '#C7E7FF',
                    'tag' => $post->tag ?: 'Post',
                    'media' => $firstAttachment['path'] ?? $post->media ?? null,
                    'media_url' => $firstAttachment['url'] ?? ($post->media ? asset('storage/' . $post->media) : null),
                    'media_type' => $firstAttachment['type'] ?? $post->media_type ?? null,
                    'attachments' => $payloadAttachments,
                ];
            });

        $trendingItems = collect($this->trendingService->getWeeklyTrending())
            ->map(fn ($item) => [
                $item['judul'],
                $item['count'] . ' postingan',
                route('timeline_home', ['book' => $item['judul']]),
            ])
            ->all();

        return view('timeline_simpanan', compact('posts', 'trendingItems'));
    }
}
