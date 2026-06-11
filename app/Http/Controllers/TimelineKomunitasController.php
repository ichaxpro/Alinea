<?php

namespace App\Http\Controllers;

use App\Models\TimelinePost;
use App\Models\TimelineComment;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use App\Http\Resources\TimelinePostResource;
use App\Http\Resources\TimelineCommentResource;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class TimelineKomunitasController extends Controller
{
    public function __construct() {}

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
                $postsQuery = TimelinePost::with(['author', 'club', 'attachments'])
                    ->whereNotNull('id_klub')
                    ->whereNull('deleted_at');

                $activeTag = request()->query('tag_filter');
                if ($activeTag) {
                    $postsQuery->where('tag', $activeTag);
                }

                $posts = TimelinePostResource::collection($postsQuery->latest()->get())->resolve();
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
        if ($files instanceof UploadedFile) {
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
                'type' => Str::before($file->getMimeType(), '/'),
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
        ]);

        if (!empty($attachments) && Schema::hasTable('timeline_attachments')) {
            $post->attachments()->createMany($attachments);
        }

        return response()->json([
            'message' => 'Postingan berhasil disimpan.',
            'post' => (new TimelinePostResource($post->load(['club', 'author', 'attachments'])))->resolve(),
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

        $comments = TimelineCommentResource::collection($query->get());

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
                'type' => Str::before($file->getMimeType(), '/'),
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
            'comment' => new TimelineCommentResource($comment),
            'comments_count' => $post->comments()->count(),
        ], 201);
    }
}
