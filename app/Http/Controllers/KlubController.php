<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\BookClub;
use App\Models\FeaturedBook;
use App\Models\TimelineAttachment;
use App\Models\TimelinePost;
use Illuminate\Validation\Rule;
use App\Models\TimelineComment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class KlubController extends Controller
{
    private function attachmentPayload(object $attachment): array
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

    private function detectMediaType(?string $mime): string
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

    private function timelineCommentPayload(TimelineComment $comment): array
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
            'absolute_time' => $comment->created_at ? Carbon::parse($comment->created_at)->locale('id')->translatedFormat('d M Y, H:i') : 'Baru saja',
        ];
    }

    private function avatarUrl(?string $username, ?string $name = null): ?string
    {
        $seed = $username ?: $name;

        if (!$seed) {
            return null;
        }

        return 'https://api.dicebear.com/7.x/thumbs/svg?seed=' . urlencode($seed);
    }

    private function memberPayload($user, string $role): array
    {
        $name = $user?->name ?? $user?->nama ?? 'Member';
        $username = $user?->username ?? null;

        return [
            'id' => $user?->id,
            'name' => $name,
            'username' => $username,
            'avatar' => $this->avatarUrl($username, $name),
            'role' => $role,
        ];
    }

    private function clubMemberRows(BookClub $club)
    {
        if (!Schema::hasTable('klub_member')) {
            return collect();
        }

        return DB::table('klub_member')
            ->leftJoin('users', 'klub_member.id_user', '=', 'users.id')
            ->where('klub_member.id_klub', $club->id)
            ->orderByRaw("CASE klub_member.role_di_klub WHEN 'owner' THEN 0 WHEN 'admin' THEN 1 ELSE 2 END")
            ->select([
                'users.id as user_id',
                'users.foto_profil',
                'users.name',
                'users.username',
                'klub_member.role_di_klub as role',
            ])
            ->get();
    }

    private function clubPayload(BookClub $club, ?int $currentUserId = null): array
    {
        $club->loadMissing(['owner']);

        $ownerUser = $club->owner;

        $ownerName = $ownerUser?->name ?? 'Admin';
        $ownerUsername = $ownerUser?->username ?? null;
        // Prefer uploaded avatar on the User model (avatar_url) when available
        $ownerAvatar = $ownerUser?->avatar_url ?? $this->avatarUrl($ownerUsername, $ownerName);

        $membersData = $this->clubMemberRows($club)->map(function ($member) use ($club) {
            $name = $member->name ?: 'Member';
            $username = $member->username ?: null;
            $role = $member->role ?? 'member';

            if ((int) ($club->id_owner ?? 0) === (int) ($member->user_id ?? 0)) {
                $role = 'owner';
            } elseif ((($member->role ?? '') === 'admin')) {
                $role = 'admin';
            }

            // Prefer user's uploaded profile photo if present
            if (!empty($member->foto_profil)) {
                $avatar = asset('storage/' . $member->foto_profil);
            } else {
                $avatar = $this->avatarUrl($username, $name);
            }

            return [
                'id' => $member->user_id,
                'name' => $name,
                'username' => $username,
                'avatar' => $avatar,
                'role' => $role,
            ];
        })->values();

        if ($membersData->isEmpty() && $ownerUser) {
            // Ensure owner appears in members list with their real avatar when no klub_member rows exist
            $ownerPayload = $this->memberPayload($ownerUser, 'owner');
            // If owner has uploaded foto_profil, override avatar
            if (!empty($ownerUser?->foto_profil)) {
                $ownerPayload['avatar'] = asset('storage/' . $ownerUser->foto_profil);
            } elseif (!empty($ownerUser?->avatar_url)) {
                $ownerPayload['avatar'] = $ownerUser->avatar_url;
            }

            $membersData->push($ownerPayload);
        }

        // Determine admin from klub_member (if any); fallback to owner. Prefer uploaded avatar when available.
        $adminMember = $membersData->firstWhere('role', 'admin');
        $adminName = $adminMember['name'] ?? $ownerName;
        $adminUsername = $adminMember['username'] ?? $ownerUsername;
        $adminAvatar = $adminMember['avatar'] ?? $ownerUser?->avatar_url ?? $this->avatarUrl($adminUsername, $adminName);

        // Use klub_member rows as the single source of truth for counts
        $membersCount = $membersData->count();

        $joined = $currentUserId
            ? $membersData->contains(fn ($member) => (int) ($member['id'] ?? 0) === $currentUserId)
            : false;

        return [
            'id' => $club->id,
            'name' => $club->nama_klub,
            'category' => $club->kategori,
            'members' => $membersCount,
            'founded' => $club->created_at ? $club->created_at->locale('id')->translatedFormat('d F Y') : null,
            'description' => (strlen($club->deskripsi) > 160 ? Str::limit($club->deskripsi, 160) : $club->deskripsi),
            'full_description' => $club->deskripsi,
            'admin' => $adminName,
            'admin_username' => $adminUsername,
            'admin_avatar' => $adminAvatar,
            'owner' => [
                'id' => $club->id_owner,
                'name' => $ownerName,
                'username' => $ownerUsername,
                'avatar' => $ownerAvatar,
                'role' => 'owner',
            ],
            'joined' => $joined,
            'foto_klub' => $club->foto_klub ? asset('storage/' . $club->foto_klub) : null,
            'members_list' => $membersData->pluck('name')->values()->all(),
            'members_data' => $membersData->toArray(),
            'recent_books' => [],
            'schedule' => $club->jadwal ?? null,
            'gradient_from' => $club->gradient_from ?? '#FFDDAF',
            'gradient_to' => $club->gradient_to ?? '#C7E7FF',
        ];
    }

    /**
     * Store a newly created book club and return JSON for immediate UI update.
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // Allowed categories come from katalog (FeaturedBook.genres)
        $allowed = FeaturedBook::all()->pluck('genres')->flatten()->unique()->values()->all();
        if (empty($allowed)) {
            // Fallback to existing klub categories if katalog is empty
            $allowed = BookClub::distinct()->pluck('kategori')->all();
        }

        $data = $request->validate([
            'nama_klub' => 'required|string|max:100',
            'kategori' => ['required', 'string', 'max:100', Rule::in($allowed)],
            'deskripsi' => 'required|string|max:500',
            'gradient_from' => 'nullable|string|max:25',
            'gradient_to' => 'nullable|string|max:25',
            'foto_klub' => 'nullable|image|max:2048',
        ]);

        $club = BookClub::create([
            'nama_klub' => $data['nama_klub'],
            'kategori' => $data['kategori'],
            'deskripsi' => $data['deskripsi'],
            'gradient_from' => $data['gradient_from'] ?? '#FFDDAF',
            'gradient_to' => $data['gradient_to'] ?? '#C7E7FF',
            'id_owner' => $user ? $user->id : null,
        ]);

        if ($user && Schema::hasTable('klub_member')) {
            DB::table('klub_member')->insert([
                'id_klub' => $club->id,
                'id_user' => $user->id,
                'role_di_klub' => 'owner',
                'joined_at' => now(),
            ]);
        }

        if ($request->hasFile('foto_klub')) {
            $path = $request->file('foto_klub')->store('klubs', 'public');
            $club->foto_klub = $path;
            $club->save();
        }

        return response()->json($this->clubPayload($club->fresh(), $user?->id), 201);
    }

    public function join(Request $request, BookClub $club)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        if (Schema::hasTable('klub_member')) {
            $alreadyJoined = DB::table('klub_member')
                ->where('id_klub', $club->id)
                ->where('id_user', $user->id)
                ->exists();

            if (!$alreadyJoined) {
                DB::table('klub_member')->insert([
                    'id_klub' => $club->id,
                    'id_user' => $user->id,
                    'role_di_klub' => 'member',
                    'joined_at' => now(),
                ]);
            }
        }

        return response()->json($this->clubPayload($club->fresh(), $user->id));
    }

    public function timelineComments(TimelinePost $post)
    {
        $comments = $post->comments()
            ->with(['author:id,name,username,foto_profil', 'attachments'])
            ->orderBy('created_at')
            ->get()
            ->map(fn (TimelineComment $comment) => $this->timelineCommentPayload($comment));

        return response()->json([
            'comments' => $comments,
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
                'type' => $this->detectMediaType($file->getMimeType()),
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
            'comment' => $this->timelineCommentPayload($comment),
            'comments_count' => $post->comments()->count(),
        ], 201);
    }

    public function leave(Request $request, BookClub $club)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        if ((int) ($club->id_owner ?? 0) === (int) $user->id) {
            return response()->json(['message' => 'Owner tidak bisa keluar dari klubnya sendiri.'], 422);
        }

        if (Schema::hasTable('klub_member')) {
            DB::table('klub_member')
                ->where('id_klub', $club->id)
                ->where('id_user', $user->id)
                ->delete();
        }

        return response()->json($this->clubPayload($club->fresh(), $user->id));
    }

    public function update(Request $request, BookClub $club)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        if ((int) ($club->id_owner ?? 0) !== (int) $user->id) {
            return response()->json(['message' => 'Hanya owner yang bisa mengubah klub.'], 403);
        }

        $allowed = FeaturedBook::all()->pluck('genres')->flatten()->unique()->values()->all();
        if (empty($allowed)) {
            $allowed = BookClub::distinct()->pluck('kategori')->all();
        }


        // Allow partial updates: only validate provided fields and preserve existing values when omitted
        // Validate inputs; allow nullable so we can distinguish omitted/empty fields
        $data = $request->validate([
            'nama_klub' => 'sometimes|nullable|string|max:100',
            'kategori' => ['sometimes','nullable', 'string', 'max:100', Rule::in($allowed)],
            'deskripsi' => 'sometimes|nullable|string|max:500',
            'gradient_from' => 'nullable|string|max:25',
            'gradient_to' => 'nullable|string|max:25',
            'foto_klub' => 'nullable|image|max:2048',
        ]);

        // Only update fields that are actually filled (non-empty). This prevents empty inputs
        // from unintentionally overwriting existing values when the user only edits one field.
        $club->update([
            'nama_klub' => $request->filled('nama_klub') ? $data['nama_klub'] : $club->nama_klub,
            'kategori' => $request->filled('kategori') ? $data['kategori'] : $club->kategori,
            'deskripsi' => $request->filled('deskripsi') ? $data['deskripsi'] : $club->deskripsi,
            'gradient_from' => $data['gradient_from'] ?? $club->gradient_from ?? '#FFDDAF',
            'gradient_to' => $data['gradient_to'] ?? $club->gradient_to ?? '#C7E7FF',
        ]);

        if ($request->hasFile('foto_klub')) {
            $path = $request->file('foto_klub')->store('klubs', 'public');
            $club->foto_klub = $path;
            $club->save();
        }

        return response()->json($this->clubPayload($club->fresh(), $user->id));
    }

    public function destroy(Request $request, BookClub $club)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        if ((int) ($club->id_owner ?? 0) !== (int) $user->id) {
            return response()->json(['message' => 'Hanya owner yang bisa menghapus klub.'], 403);
        }

        if (Schema::hasTable('klub_member')) {
            DB::table('klub_member')->where('id_klub', $club->id)->delete();
        }

        $club->delete();

        return response()->json(['message' => 'Klub berhasil dihapus.']);
    }

    /**
     * Show klub page with clubs loaded from database.
     */
    public function index()
    {
        $currentUser = Auth::user();
        $clubs = BookClub::with('owner')->get()->map(fn ($club) => $this->clubPayload($club, $currentUser?->id));

        // Categories should align with katalog genres (FeaturedBook.genres)
        $categories = FeaturedBook::all()->pluck('genres')->flatten()->unique()->sort()->values();

        return view('klub', compact('clubs', 'categories', 'currentUser'));
    }

    /**
     * Return JSON payload for a single club (used by client to re-sync state).
     */
    public function payload(BookClub $club)
    {
        $user = Auth::user();
        return response()->json($this->clubPayload($club, $user?->id));
    }

    /**
     * Timeline komunitas — moved from routes/web.php to controller for better separation.
     */
    public function timelineKomunitas()
    {
        $popularClubs = collect();
        $joinedClubs = collect();
        $posts = collect();

        $currentUser = Auth::user();

        if (Schema::hasTable('klub')) {
            $popularClubs = DB::table('klub')
                ->leftJoin('klub_member', 'klub.id', '=', 'klub_member.id_klub')
                ->select([
                    'klub.id',
                    'klub.nama_klub',
                    DB::raw('COUNT(DISTINCT klub_member.id_user) as member_count'),
                ])
                ->groupBy('klub.id', 'klub.nama_klub')
                ->orderByDesc('member_count')
                ->orderBy('klub.nama_klub')
                ->limit(5)
                ->get();

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

            if (Schema::hasTable('timeline_posts') && $joinedClubs->isNotEmpty()) {
                $posts = DB::table('timeline_posts')
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
                    ->whereIn('timeline_posts.id_klub', $joinedClubs->pluck('id')->all())
                    ->select([
                        'timeline_posts.id',
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
                    $payloadAttachments = $attachments->map(fn ($attachment) => $this->attachmentPayload($attachment))->values()->all();
                    $firstAttachment = $payloadAttachments[0] ?? null;

                    return [
                        'id' => $post->id,
                        'name' => $post->name ?? 'Pengguna',
                        'username' => $post->handle ? ltrim($post->handle, '@') : null,
                        'profile_url' => $post->handle ? route('profile.by_username', ['username' => ltrim($post->handle, '@')]) : '#',
                        'handle' => $post->handle ? '@' . ltrim($post->handle, '@') : '@pengguna',
                        'location' => $post->location ?: 'Online',
                        'time' => $post->created_at ? \Carbon\Carbon::parse($post->created_at)->locale('id')->diffForHumans() : 'Baru saja',
                        'absolute_time' => $post->created_at ? \Carbon\Carbon::parse($post->created_at)->locale('id')->translatedFormat('d M Y, H:i') : 'Baru saja',
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

        return view('timeline_komunitas', compact('popularClubs', 'joinedClubs', 'posts'));
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
                'type' => $this->detectMediaType($file->getMimeType()),
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
                'name' => $currentUser->name,
                'username' => $currentUser->username,
                'profile_url' => $currentUser->username ? route('profile.by_username', ['username' => ltrim($currentUser->username, '@')]) : '#',
                'handle' => $currentUser->username ? '@' . ltrim($currentUser->username, '@') : '@pengguna',
                'location' => $currentUser->kota ?: 'Online',
                'time' => $post->created_at ? \Carbon\Carbon::parse($post->created_at)->locale('id')->diffForHumans() : 'Baru saja',
                'absolute_time' => $post->created_at ? \Carbon\Carbon::parse($post->created_at)->locale('id')->translatedFormat('d M Y, H:i') : 'Baru saja',
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

    public function kickMember(Request $request, BookClub $club, int $userId) {
        $currentUser = Auth::user();

        if (!$currentUser) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $myRow = DB::table('klub_member')
            ->where('id_klub', $club->id)
            ->where('id_user', $currentUser->id)
            ->first();
        
        $isOwner = (int) ($club->id_owner ?? 0) === (int) $currentUser->id;
        $isAdmin = $myRow && in_array($myRow->role_di_klub, ['admin', 'moderator']);

        if (!$isOwner && !$isAdmin) {
            return response()->json(['message' => 'Hanya owner atau admin yang bisa kick member.'], 403);
        } 

        if ((int) ($club->id_owner ?? 0) === (int) $userId) {
            return response()->json(['message' => 'Owner tidak bisa di-kick dari klub.'], 422);
        }

        if (!$isOwner && $isAdmin) {
            $targetRow = DB::table('klub_member')
                ->where('id_klub', $club->id)
                ->where('id_user', $userId)
                ->first();
            
            if ($targetRow && in_array($targetRow->role_di_klub, ['admin', 'moderator'])) {
                return response()->json(['message' => 'Admin tidak bisa kick admin lain.'], 403);
            }
        }

        if ((int) $userId === (int) $currentUser->id) {
            return response()->json(['message' => 'Gunakan fitur "Keluar Klub" untuk meninggalkan klub.'], 422);
        }

        if (Schema::hasTable('klub_member')) {
            DB::table('klub_member')
                ->where('id_klub', $club->id)
                ->where('id_user', $userId)
                ->delete();
        }

        return response()->json($this->clubPayload($club->fresh(), $currentUser->id));
    }

    public function updateMemberRole(Request $request, BookClub $club, int $userId) {
        $currentUser = Auth::user();

        if (!$currentUser) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $isOwner = (int) ($club->id_owner ?? 0) === (int) $currentUser->id;

        if (!$isOwner) {
            return response()->json(['message' => 'Hanya owner yang bisa mengubah role member.'], 403);
        }

        $validated = $request->validate([
            'role' => ['required', 'string', 'in:member,admin,owner'],
        ]);

        $newRole = $validated['role'];

        if (Schema::hasTable('klub_member')) {
            $targetExists = DB::table('klub_member')
                ->where('id_klub', $club->id)
                ->where('id_user', $userId)
                ->exists();
            if (!$targetExists) {
                return response()->json(['message' => 'User bukan anggota klub ini.'], 404);
            }
        }

        if ($newRole === 'owner') {
            $club->id_owner = $userId;
            $club->save();

            DB::table('klub_member')
                ->where('id_klub', $club->id)
                ->where('id_user', $currentUser->id)
                ->update(['role_di_klub' => 'admin']);
            
            DB::table('klub_member')
                ->where('id_klub', $club->id)
                ->where('id_user', $userId)
                ->update(['role_di_klub' => 'owner']);
        } else {
            DB::table('klub_member')
                ->where('id_klub', $club->id)
                ->where('id_user', $userId)
                ->update(['role_di_klub' => $newRole]);
        }

        return response()->json($this->clubPayload($club->fresh(), $currentUser->id));
    }
}
