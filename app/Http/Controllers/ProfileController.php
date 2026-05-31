<?php

namespace App\Http\Controllers;

use App\Models\Follow;
use App\Models\PersonalBook;
use App\Models\TimelinePost;
use App\Models\User;
use App\Services\AchievementService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function __construct(
        protected AchievementService $achievementService
    ) {}

    public function show(?string $username = null) {
        if ($username) {
            $user = User::where('username', $username)->firstOrFail();
        } else {
            $user = Auth::user();
            if (!$user) return redirect()->route('login');
        }

        $currentUser = Auth::user();
        $isOwnProfile = $currentUser && $currentUser->id === $user->id;

        if ($isOwnProfile) {
            $this->achievementService->checkAndGrant($user);
        }

        $posts = TimelinePost::with(['author', 'attachments'])
            ->where('id_user', $user->id)
            ->orderByDesc('created_at')
            ->get()
            ->map(fn ($post) => [
                'id' => $post->id,
                'name' => $post->author?->name ?? 'Pengguna',
                'handle' => $post->author?->username ? '@' . ltrim($post->author->username, '@') : '@pengguna',
                'avatar_url' => $post->author?->avatar_url,
                'location' => $post->author?->kota ?: 'Online',
                'time' => $post->created_at ? Carbon::parse($post->created_at)->diffForHumans() : 'Baru saja',
                'book' => $post->judul_buku_dibahas,
                'body' => $post->pesan,
                'tag' => $post->tag ?: 'Post',
                'comments' => (string) $post->comments()->count(),
                'likes_base' => 0, 'likes_label' => '0', 'liked' => false,
                'bookmarked' => $currentUser ? \App\Models\PostBookmark::where('id_post', $post->id)->where('id_user', $currentUser->id)->exists() : false,
                'media' => $post->media,
                'media_url' => $post->media ? asset('storage/' . $post->media) : null,
                'media_type' => $post->media_type,
                'media_original_name' => $post->media_original_name,
                'attachments' => $post->attachments->map(fn ($attachment) => [
                    'url' => asset('storage/' . $attachment->path),
                    'type' => $attachment->type,
                    'original_name' => $attachment->original_name,
                ])->values()->all(),
            ]);

        $achievements = $user->achievements()->get();

        $readingBooks = PersonalBook::where('user_id', $user->id)
            ->whereNotNull('reading_status')
            ->orderByDesc('updated_at')
            ->get();

        $readingNow = $readingBooks->where('reading_status', 'sedang_dibaca');
        $finishedBooks = $readingBooks->where('reading_status', 'selesai');

        $mediaPosts = TimelinePost::with(['author', 'attachments'])
            ->where('id_user', $user->id)->whereNotNull('media')
            ->orderByDesc('created_at')
            ->get()
            ->map(fn ($post) => [
                'id' => $post->id,
                'name' => $post->author?->name ?? 'Pengguna',
                'handle' => $post->author?->username ? '@' . ltrim($post->author->username, '@') : '@pengguna',
                'avatar_url' => $post->author?->avatar_url,
                'location' => $post->author?->kota ?: 'Online',
                'time' => Carbon::parse($post->created_at)->diffForHumans(),
                'tag' => $post->tag ?: 'Post',
                'caption' => $post->pesan,
                'attachments' => $post->attachments->isNotEmpty()
                    ? $post->attachments->map(fn ($attachment) => [
                        'type' => $attachment->type ?: 'image',
                        'src' => $attachment->path,
                        'label' => $attachment->original_name,
                    ])->values()->all()
                    : ($post->media ? [
                        ['type' => $post->media_type ?: 'image', 'src' => $post->media, 'label' => $post->media_original_name]
                    ] : []),
                'comments' => (string) $post->comments()->count(),
                'likes_label' => '0', 'liked' => false,
                'bookmarked' => $currentUser ? \App\Models\PostBookmark::where('id_post', $post->id)->where('id_user', $currentUser->id)->exists() : false,
                ]);

        $followersCount = $user->followersCount();
        $followingCount = $user->followingCount();

        $isFollowing = false;
        if ($currentUser && !$isOwnProfile) {
            $isFollowing = Follow::where('follower_id', $currentUser->id)
                ->where('following_id', $user->id)->exists();
        }

        return view('timeline_profile', compact(
            'user', 'posts', 'achievements', 'readingNow', 'finishedBooks', 'mediaPosts', 'followersCount', 'followingCount', 'isOwnProfile', 'isFollowing', 'currentUser',
        ));
    }

    public function edit() {
        $user = Auth::user();
        if (!$user) return redirect()->route('login');
        return view('timeline_profile_edit', compact('user'));
    }

    public function update(Request $request) {
        /** @var User|null $user */
        $user = Auth::user();
        if (!$user) return response()->json(['message' => 'Unauthorized'], 401);

        $data = $request->validate([
            'name' => 'sometimes|string|max:255',
            'username' => 'sometimes|string|max:50|unique:users,username,' . $user->id,
            'deskripsi' => 'nullable|string|max:500',
            'kota' => 'nullable|string|max:100',
            'no_telp' => 'nullable|string|max:20',
        ]);

        $user->update($data);
        return redirect()->route('timeline_profile')->with('success', 'Profil berhasil diperbarui.');
    }

    public function updateFoto(Request $request) {
        /** @var User|null $user */
        $user = Auth::user();
        if (!$user) return response()->json(['message' => 'Unauthorized'], 401);

        $request->validate(['foto_profil' => 'required|image|max:2048']);
        $path = $request->file('foto_profil')->store('avatars', 'public');
        $user->foto_profil = $path;
        $user->save();

        return response()->json(['message' => 'Foto profil berhasil diperbarui.', 'avatar_url' => $user->avatar_url]);
    }

    public function toggleFollow(User $user) {
        $currentUser = Auth::user();
        if (!$currentUser) return response()->json(['message' => 'Unauthorized'], 401);
        if ($currentUser->id === $user->id) return response()->json(['message' => 'Tidak bisa follow diri sendiri'], 422);

        $existing = Follow::where('follower_id', $currentUser->id)
            ->where('following_id', $user->id)->first();
        
        if ($existing) {
            $existing->delete();
            $following = false;
        } else {
            Follow::create(['follower_id' => $currentUser->id, 'following_id' => $user->id]);
            $following = true;
        }

        return response()->json([
            'following' => $following,
            'followers_count' => $user->followersCount(),
        ]);
    }

    public function followersList(User $user) {
        $currentUser = Auth::user();

        $followers = $user->followers()
            ->with('follower')
            ->latest()
            ->get()
            ->map(fn ($follow) => [
                'id' => $follow->follower->id,
                'name' => $follow->follower->name,
                'username' => $follow->follower->username,
                'avatar_url' => $follow->follower->avatar_url,
                'is_following' => $currentUser ? Follow::where('follower_id', $currentUser->id)
                        ->where('following_id', $follow->follower->id)
                        ->exists()
                    : false,
            ]);
            return response()->json(['users' => $followers]);
    }

    public function followingList(User $user) {
        $currentUser = Auth::user();

        $following = $user->following()
            ->with('following')
            ->latest()
            ->get()
            ->map(fn ($follow) => [
                'id' => $follow->following->id,
                'name' => $follow->following->name,
                'username' => $follow->following->username,
                'avatar_url' => $follow->following->avatar_url,
                'is_following' => $currentUser ? Follow::where('follower_id', $currentUser->id)
                        ->where('following_id', $follow->following->id)
                        ->exists()
                    : false,
            ]);
            return response()->json(['users' => $following]);
    }

    public function storeReadingBook(Request $request) {
        $user = Auth::user();
        if (!$user) return response()->json(['message' => 'Unauthorized'], 401);

        $data = $request->validate([
            'judul' => 'required|string|max:255',
            'penulis' => 'required|string|max:255',
            'cover_url' => 'nullable|string|max:255',
            'reading_status' => 'required|in:sedang_dibaca,selesai,diinginkan',
        ]);

        $book = PersonalBook::create([
            'user_id' => $user->id,
            'judul' => $data['judul'],
            'penulis' => $data['penulis'],
            'cover_url' => $data['cover_url'] ?? null,
            'reading_status' => $data['reading_status'],
            'is_available' => false,
            'status' => 'tidak_tersedia',
        ]);

        return response()->json(['message' => 'Buku berhasil ditambahkan.', 'book' => $book], 201);
    }

    public function updateReadingBook(Request $request, PersonalBook $book) {
        $user = Auth::user();
        if (!$user || $book->user_id !== $user->id) return response()->json(['message' => 'Forbidden'], 403);

        $data = $request->validate(['reading_status' => 'required|in:sedang_dibaca,selesai,diinginkan']);
        $book->update($data);

        return response()->json(['message' => 'Status baca diperbarui.', 'book' => $book->fresh()]);
    }

    public function destroyReadingBook(PersonalBook $book) {
        $user = Auth::user();
        if (!$user || $book->user_id !== $user->id) return response()->json(['message' => 'Forbidden'], 403);

        $book->delete();
        return response()->json(['message' => 'Buku dihapus dari riwayat baca.']);
    }
}
