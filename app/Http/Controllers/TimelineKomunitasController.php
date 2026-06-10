<?php

namespace App\Http\Controllers;

use App\Models\TimelinePost;
use App\Models\TimelineComment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use App\Services\TimelineFormatterService;

class TimelineKomunitasController extends Controller
{
    public function __construct(
        protected TimelineFormatterService $timelineFormatterService
    ) {}

    public function timelineKomunitas()
    {
        $popularClubs = collect();
        $joinedClubs = collect();
        $posts = collect();
        $trendingItems = [];

        $currentUser = Auth::user();

        if (Schema::hasTable('klub')) {
            $startOfWeek = \Carbon\Carbon::now()->startOfWeek();
            $popularClubs = DB::table('klub')
                ->join('klub_member', function ($join) use ($startOfWeek) {
                    $join->on('klub.id', '=', 'klub_member.id_klub')
                         ->where('klub_member.joined_at', '>=', $startOfWeek);
                })
                ->select([
                    'klub.id',
                    'klub.nama_klub',
                    DB::raw('COUNT(DISTINCT klub_member.id_user) as new_members_count'),
                ])
                ->groupBy('klub.id', 'klub.nama_klub')
                ->orderByDesc('new_members_count')
                ->orderBy('klub.nama_klub')
                ->limit(5)
                ->get();

            $trendingItems = $popularClubs->map(function ($club) {
                return [
                    $club->nama_klub,
                    $club->new_members_count . ' Member Baru',
                ];
            })->all();

            if ($currentUser && Schema::hasTable('klub_member')) {
                $joinedClubs = DB::table('klub_member')
                    ->join('klub', 'klub_member.id_klub', '=', 'klub.id')
                    ->where('klub_member.id_user', $currentUser->id)
                    ->select([
                        'klub.id',
                        'klub.nama_klub',
                    ])
                    ->distinct()
                    ->orderBy('klub.nama_klub')
                    ->get();
            }

            if (Schema::hasTable('timeline_posts')) {
                $postsQuery = DB::table('timeline_posts')
                    ->leftJoin('users', 'timeline_posts.id_user', '=', 'users.id')
                    ->leftJoin('klub', 'timeline_posts.id_klub', '=', 'klub.id')
                    ->leftJoin(DB::raw('(select id_post, count(*) as comments_count from timeline_comments group by id_post) as comments'), function ($join) {
                        $join->on('timeline_posts.id', '=', 'comments.id_post');
                    })
                    ->leftJoin(DB::raw('(select id_post, count(*) as likes_count from timeline_likes group by id_post) as likes'), function ($join) {
                        $join->on('timeline_posts.id', '=', 'likes.id_post');
                    })
                    ->leftJoin('timeline_likes as user_like', function ($join) use ($currentUser) {
                        $join->on('timeline_posts.id', '=', 'user_like.id_post')
                             ->where('user_like.id_user', '=', $currentUser ? $currentUser->id : 0);
                    })
                    ->leftJoin('post_bookmarks as user_bookmark', function ($join) use ($currentUser) {
                        $join->on('timeline_posts.id', '=', 'user_bookmark.id_post')
                             ->where('user_bookmark.id_user', '=', $currentUser ? $currentUser->id : 0);
                    })
                    ->whereNotNull('timeline_posts.id_klub')
                    ->whereNull('timeline_posts.deleted_at');

                $activeTag = request()->query('tag_filter');
                if ($activeTag) {
                    $postsQuery->where('timeline_posts.tag', $activeTag);
                }

                $posts = $postsQuery->select([
                        'timeline_posts.id',
                        'timeline_posts.id_user as user_id',
                        'timeline_posts.media',
                        'timeline_posts.media_type',
                        'timeline_posts.media_original_name',
                        'timeline_posts.media_size',
                        'timeline_posts.judul_buku_dibahas as book',
                        'timeline_posts.pesan as body',
                        'timeline_posts.tag',
                        'timeline_posts.created_at',
                        'users.name',
                        'users.username as handle',
                        'users.kota as location',
                        'users.foto_profil',
                        'klub.nama_klub as klub',
                        'klub.gradient_from as avatar_from',
                        'klub.gradient_to as avatar_to',
                        DB::raw('COALESCE(comments.comments_count, 0) as comments'),
                        DB::raw('COALESCE(likes.likes_count, 0) as likes_base'),
                        DB::raw('CASE WHEN user_like.id IS NOT NULL THEN 1 ELSE 0 END as is_liked'),
                        DB::raw('CASE WHEN user_bookmark.id IS NOT NULL THEN 1 ELSE 0 END as is_bookmarked'),
                    ])
                    ->orderByDesc('timeline_posts.created_at')
                    ->get();

                $postAttachments = collect();
                $postIds = $posts->pluck('id')->all();
                if (!empty($postIds) && Schema::hasTable('timeline_attachments')) {
                    $postAttachments = DB::table('timeline_attachments')
                        ->where('attachable_type', TimelinePost::class)
                        ->whereIn('attachable_id', $postIds)
                        ->orderBy('sort_order')
                        ->orderBy('id')
                        ->get()
                        ->groupBy('attachable_id');
                }

                $posts = $posts->map(function ($post) use ($postAttachments) {
                    $attachments = $postAttachments->get($post->id, collect());
                    $payloadAttachments = $attachments->map(fn ($attachment) => $this->timelineFormatterService->attachmentPayload($attachment))->values()->all();
                    $firstAttachment = $payloadAttachments[0] ?? null;

                    return [
                        'id' => $post->id,
                        'user_id' => $post->user_id,
                        'name' => $post->name ?? 'Pengguna',
                        'username' => $post->handle ? ltrim($post->handle, '@') : null,
                        'profile_url' => $post->handle ? route('profile.by_username', ['username' => ltrim($post->handle, '@')]) : '#',
                        'handle' => $post->handle ? '@' . ltrim($post->handle, '@') : '@pengguna',
                        'location' => $post->location ?: 'Online',
                        'time' => $post->created_at ? \Carbon\Carbon::parse($post->created_at)->locale('id')->diffForHumans() : 'Baru saja',
                        'absolute_time' => $post->created_at ? \Carbon\Carbon::parse($post->created_at)->timezone('Asia/Jakarta')->locale('id')->translatedFormat('d M Y, H:i') : 'Baru saja',
                        'book' => $post->book,
                        'klub' => $post->klub,
                        'body' => $post->body,
                        'comments' => (string) $post->comments,
                        'likes_base' => (int) $post->likes_base,
                        'likes_label' => $post->likes_base >= 1000 ? round($post->likes_base/1000, 1) . 'K' : (string) $post->likes_base,
                        'liked' => (bool) $post->is_liked,
                        'bookmarked' => (bool) $post->is_bookmarked,
                        'avatar_url' => $post->foto_profil ? asset('storage/' . $post->foto_profil) : null,
                        'avatar_from' => $post->avatar_from ?: '#FFDDAF',
                        'avatar_to' => $post->avatar_to ?: '#C7E7FF',
                        'tag' => $post->tag ?: 'Post',
                        'media' => $firstAttachment['path'] ?? $post->media ?? null,
                        'media_url' => $firstAttachment['url'] ?? ($post->media ? asset('storage/' . $post->media) : null),
                        'media_type' => $firstAttachment['type'] ?? $post->media_type ?? null,
                        'media_original_name' => $firstAttachment['original_name'] ?? $post->media_original_name ?? null,
                        'media_size' => $firstAttachment['size'] ?? $post->media_size ?? null,
                        'attachments' => $payloadAttachments,
                    ];
                });
            }
        }

        return view('timeline_komunitas', compact('popularClubs', 'joinedClubs', 'posts', 'activeTag', 'trendingItems'));
    }

    public function storeTimelinePost(Request $request)
    {
        $validated = $request->validate([
            'id_klub' => ['required', 'integer', Rule::exists('klub', 'id')],
            'judul_buku_dibahas' => ['nullable', 'string', 'max:120'],
            'pesan' => ['required', 'string', 'max:250'],
            'tag' => ['nullable', 'string', 'max:30'],
            'media' => ['nullable', 'array', 'max:4'],
            'media.*' => ['file', 'max:102400'],
        ]);

        $currentUser = $request->user();

        if (!$currentUser) {
            return response()->json(['message' => 'Silakan login terlebih dahulu.'], 401);
        }

        if (Schema::hasTable('klub_member')) {
            $isMember = DB::table('klub_member')
                ->where('id_klub', $validated['id_klub'])
                ->where('id_user', $currentUser->id)
                ->exists();

            if (!$isMember) {
                return response()->json(['message' => 'Kamu hanya bisa posting ke klub yang kamu ikuti.'], 403);
            }
        }

        $files = $request->file('media', []);
        if ($files instanceof \Illuminate\Http\UploadedFile) {
            $files = [$files];
        }

        $attachments = [];
        foreach (array_values($files) as $index => $file) {
            if (!$file) {
                continue;
            }

            $path = $file->store('timeline_media', 'public');
            $attachments[] = [
                'path' => $path,
                'type' => $this->timelineFormatterService->detectMediaType($file->getMimeType()),
                'original_name' => $file->getClientOriginalName(),
                'size' => $file->getSize(),
                'sort_order' => $index,
            ];
        }

        $post = TimelinePost::create([
            'id_user' => $currentUser->id,
            'id_klub' => $validated['id_klub'],
            'judul_buku_dibahas' => $validated['judul_buku_dibahas'] ?? null,
            'pesan' => $validated['pesan'],
            'tag' => $validated['tag'] ?? 'Post',
            'media' => $attachments[0]['path'] ?? null,
            'media_type' => $attachments[0]['type'] ?? null,
            'media_original_name' => $attachments[0]['original_name'] ?? null,
            'media_size' => $attachments[0]['size'] ?? null,
        ]);

        if (!empty($attachments) && Schema::hasTable('timeline_attachments')) {
            $post->attachments()->createMany($attachments);
        }

        $club = DB::table('klub')
            ->where('id', $validated['id_klub'])
            ->select(['nama_klub', 'gradient_from', 'gradient_to'])
            ->first();

        return response()->json([
            'message' => 'Postingan berhasil disimpan.',
            'post' => [
                'id' => $post->id,
                'user_id' => $currentUser->id,
                'name' => $currentUser->name,
                'username' => $currentUser->username,
                'profile_url' => $currentUser->username ? route('profile.by_username', ['username' => ltrim($currentUser->username, '@')]) : '#',
                'handle' => $currentUser->username ? '@' . ltrim($currentUser->username, '@') : '@pengguna',
                'location' => $currentUser->kota ?: 'Online',
                'time' => $post->created_at ? \Carbon\Carbon::parse($post->created_at)->locale('id')->diffForHumans() : 'Baru saja',
                'absolute_time' => $post->created_at ? \Carbon\Carbon::parse($post->created_at)->timezone('Asia/Jakarta')->locale('id')->translatedFormat('d M Y, H:i') : 'Baru saja',
                'book' => $post->judul_buku_dibahas,
                'klub' => $club?->nama_klub,
                'body' => $post->pesan,
                'comments' => '0',
                'likes_base' => 0,
                'likes_label' => '0',
                'liked' => false,
                'avatar_url' => $currentUser->avatar_url,
                'avatar_from' => $club?->gradient_from ?: '#FFDDAF',
                'avatar_to' => $club?->gradient_to ?: '#C7E7FF',
                'tag' => $post->tag ?: 'Post',
                'media_url' => $post->media ? asset('storage/' . $post->media) : null,
                'media_type' => $post->media_type,
                'media_original_name' => $post->media_original_name,
                'media_size' => $post->media_size,
                'attachments' => array_map(function (array $attachment) {
                    return [
                        'path' => $attachment['path'],
                        'url' => asset('storage/' . $attachment['path']),
                        'type' => $attachment['type'],
                        'original_name' => $attachment['original_name'],
                        'size' => $attachment['size'],
                        'sort_order' => $attachment['sort_order'],
                    ];
                }, $attachments),
            ],
        ], 201);
    }

    public function timelineComments(Request $request, TimelinePost $post)
    {
        $limit = $request->query('limit');

        $query = $post->comments()
            ->with(['author:id,name,username,foto_profil', 'attachments'])
            ->orderBy('created_at');

        $total = $query->count();

        if ($limit) {
            $query->limit((int) $limit);
        }

        $comments = $query->get()
            ->map(fn (TimelineComment $comment) => $this->timelineFormatterService->timelineCommentPayload($comment));

        return response()->json([
            'comments' => $comments,
            'total' => $total,
        ]);
    }

    public function storeTimelineComment(Request $request, TimelinePost $post)
    {
        $currentUser = Auth::user();

        if (!$currentUser) {
            return response()->json([
                'message' => 'Silakan login untuk mengirim komentar.',
            ], 401);
        }

        $validated = $request->validate([
            'isi_komentar' => ['required', 'string', 'max:500'],
            'media' => ['nullable'],
            'media.*' => ['file', 'max:10240'],
        ]);

        $files = $request->file('media', []);
        if ($files instanceof \Illuminate\Http\UploadedFile) {
            $files = [$files];
        }

        $attachments = [];
        foreach (array_values($files) as $index => $file) {
            if (!$file) {
                continue;
            }

            $path = $file->store('timeline_comments', 'public');
            $attachments[] = [
                'path' => $path,
                'type' => $this->timelineFormatterService->detectMediaType($file->getMimeType()),
                'original_name' => $file->getClientOriginalName(),
                'size' => $file->getSize(),
                'sort_order' => $index,
            ];
        }

        $comment = TimelineComment::create([
            'id_post' => $post->id,
            'id_user' => $currentUser->id,
            'isi_komentar' => $validated['isi_komentar'],
            'media' => $attachments[0]['path'] ?? null,
            'media_type' => $attachments[0]['type'] ?? null,
            'media_original_name' => $attachments[0]['original_name'] ?? null,
            'media_size' => $attachments[0]['size'] ?? null,
        ]);

        if (!empty($attachments) && Schema::hasTable('timeline_attachments')) {
            $comment->attachments()->createMany($attachments);
        }

        $comment->load(['author:id,name,username,foto_profil', 'attachments']);

        return response()->json([
            'message' => 'Komentar berhasil dikirim.',
            'comment' => $this->timelineFormatterService->timelineCommentPayload($comment),
            'comments_count' => $post->comments()->count(),
        ], 201);
    }
}
