<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\BookClub;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
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
        $ownerAvatar = $this->avatarUrl($ownerUsername, $ownerName);

        $membersData = $this->clubMemberRows($club)->map(function ($member) use ($club) {
            $name = $member->name ?: 'Member';
            $username = $member->username ?: null;
            $role = $member->role ?? 'member';

            if ((int) ($club->id_owner ?? 0) === (int) ($member->user_id ?? 0)) {
                $role = 'owner';
            } elseif ((($member->role ?? '') === 'admin')) {
                $role = 'admin';
            }

            return [
                'id' => $member->user_id,
                'name' => $name,
                'username' => $username,
                'avatar' => $this->avatarUrl($username, $name),
                'role' => $role,
            ];
        })->values();

        if ($membersData->isEmpty() && $ownerUser) {
            $membersData->push($this->memberPayload($ownerUser, 'owner'));
        }

        // Determine admin from klub_member (if any); fallback to owner
        $adminMember = $membersData->firstWhere('role', 'admin');
        $adminName = $adminMember['name'] ?? $ownerName;
        $adminUsername = $adminMember['username'] ?? $ownerUsername;
        $adminAvatar = $this->avatarUrl($adminUsername, $adminName);

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

        $data = $request->validate([
            'nama_klub' => 'required|string|max:100',
            'kategori' => 'required|string|max:100',
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

    /**
     * Show klub page with clubs loaded from database.
     */
    public function index()
    {
        $currentUser = Auth::user();
        $clubs = BookClub::with('owner')->get()->map(fn ($club) => $this->clubPayload($club, $currentUser?->id));

        $categories = BookClub::distinct()->pluck('kategori')->sort()->values();

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
}
