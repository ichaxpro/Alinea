<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\BookClub;
use App\Models\FeaturedBook;
use App\Models\TimelinePost;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class KlubController extends Controller
{
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
                try {
                    $avatar = Storage::disk('public')->url($member->foto_profil);
                } catch (\Throwable $e) {
                    $avatar = $this->avatarUrl($username, $name);
                }
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
                try {
                    $ownerPayload['avatar'] = Storage::disk('public')->url($ownerUser->foto_profil);
                } catch (\Throwable $e) {
                    // keep existing avatar
                }
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
            'founded' => $club->created_at ? $club->created_at->format('d F Y') : null,
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
                'role_di_klub' => 'moderator',
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
                    ->select(['klub.id', 'klub.nama_klub'])
                    ->orderBy('klub.nama_klub')
                    ->get();
            }
        }

        if (Schema::hasTable('timeline_posts') && $joinedClubs->isNotEmpty()) {
            $posts = DB::table('timeline_posts')
                ->leftJoin('users', 'timeline_posts.id_user', '=', 'users.id')
                ->leftJoin('klub', 'timeline_posts.id_klub', '=', 'klub.id')
                ->leftJoin(DB::raw('(select id_post, count(*) as comments_count from timeline_comments group by id_post) as comments'), function ($join) {
                    $join->on('timeline_posts.id', '=', 'comments.id_post');
                })
                ->whereIn('klub.nama_klub', $joinedClubs->pluck('nama_klub')->all())
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
                    'klub.nama_klub as klub',
                    'klub.gradient_from as avatar_from',
                    'klub.gradient_to as avatar_to',
                    DB::raw('COALESCE(comments.comments_count, 0) as comments'),
                    DB::raw('0 as likes_base'),
                ])
                ->orderByDesc('timeline_posts.created_at')
                ->get()
                ->map(function ($post) {
                    return [
                        'id' => $post->id,
                        'name' => $post->name ?? 'Pengguna',
                        'handle' => $post->handle ? '@' . ltrim($post->handle, '@') : '@pengguna',
                        'location' => $post->location ?: 'Online',
                        'time' => $post->created_at ? Carbon::parse($post->created_at)->diffForHumans() : 'Baru saja',
                        'book' => $post->book,
                        'klub' => $post->klub,
                        'body' => $post->body,
                        'comments' => (string) $post->comments,
                        'likes_base' => (int) $post->likes_base,
                        'likes_label' => (string) $post->likes_base,
                        'liked' => false,
                        'avatar_from' => $post->avatar_from ?: '#FFDDAF',
                        'avatar_to' => $post->avatar_to ?: '#C7E7FF',
                        'tag' => $post->tag ?: 'Post',
                        'media' => $post->media ?? null,
                        'media_url' => $post->media ? asset('storage/' . $post->media) : null,
                        'media_type' => $post->media_type ?? null,
                        'media_original_name' => $post->media_original_name ?? null,
                        'media_size' => $post->media_size ?? null,
                    ];
                });
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
            'media' => ['nullable', 'file', 'max:10240'], // max 10MB by default
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

        $mediaPath = null;
        $mediaType = null;
        $mediaOriginal = null;
        $mediaSize = null;

        if ($request->hasFile('media')) {
            $file = $request->file('media');
            $mediaPath = $file->store('timeline_media', 'public');
            $mime = $file->getMimeType() ?: '';
            if (str_starts_with($mime, 'image/')) {
                $mediaType = 'image';
            } elseif (str_starts_with($mime, 'video/')) {
                $mediaType = 'video';
            } else {
                $mediaType = 'file';
            }
            $mediaOriginal = $file->getClientOriginalName();
            $mediaSize = $file->getSize();
        }

        $post = TimelinePost::create([
            'id_user' => $currentUser->id,
            'id_klub' => $validated['id_klub'],
            'judul_buku_dibahas' => $validated['judul_buku_dibahas'] ?? null,
            'pesan' => $validated['pesan'],
            'tag' => $validated['tag'] ?? 'Post',
            'media' => $mediaPath,
            'media_type' => $mediaType,
            'media_original_name' => $mediaOriginal,
            'media_size' => $mediaSize,
        ]);

        $club = DB::table('klub')
            ->where('id', $validated['id_klub'])
            ->select(['nama_klub', 'gradient_from', 'gradient_to'])
            ->first();

        return response()->json([
            'message' => 'Postingan berhasil disimpan.',
            'post' => [
                'id' => $post->id,
                'name' => $currentUser->name,
                'handle' => $currentUser->username ? '@' . ltrim($currentUser->username, '@') : '@pengguna',
                'location' => $currentUser->kota ?: 'Online',
                'time' => 'Baru saja',
                'book' => $post->judul_buku_dibahas,
                'klub' => $club?->nama_klub,
                'body' => $post->pesan,
                'comments' => '0',
                'likes_base' => 0,
                'likes_label' => '0',
                'liked' => false,
                'avatar_from' => $club?->gradient_from ?: '#FFDDAF',
                'avatar_to' => $club?->gradient_to ?: '#C7E7FF',
                'tag' => $post->tag ?: 'Post',
                'media_url' => $post->media ? asset('storage/' . $post->media) : null,
                'media_type' => $post->media_type,
                'media_original_name' => $post->media_original_name,
                'media_size' => $post->media_size,
            ],
        ], 201);
    }
}
